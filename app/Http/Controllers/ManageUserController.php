<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Events\UserCreatedOrModified;

class ManageUserController extends Controller
{
    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $users = User::where('business_id', $business_id)
                        ->user()
                        ->where('is_cmmsn_agnt', 0)
                        ->with(['roles', 'permissions'])
                        ->select(['id', 'username', 'first_name', 'last_name', 'email', 'allow_login', 'status',
                            'sales_target', 'sales_target_daily', 'sales_target_weekly', 'sales_target_monthly', 'updated_at',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name")]);

            if (request()->filled('role_id')) {
                $roleId = request()->get('role_id');
                $users->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('id', $roleId);
                });
            }

            if (request()->filled('location_id')) {
                $locationId = request()->get('location_id');
                $permNames = ['access_all_locations', 'location.'.$locationId];
                $users->where(function ($q) use ($permNames) {
                    $q->whereHas('permissions', function ($p) use ($permNames) {
                        $p->whereIn('name', $permNames);
                    })->orWhereHas('roles.permissions', function ($p) use ($permNames) {
                        $p->whereIn('name', $permNames);
                    });
                });
            }

            $locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
            $adminCount = $this->listedAdminCount((int) $business_id);

            return Datatables::of($users)
                ->addColumn('user_cell', function ($row) {
                    $name = e(trim($row->full_name));
                    $email = e($row->email);

                    return '<a class="ls-user-name" href="'.action([\App\Http\Controllers\ManageUserController::class, 'show'], [$row->id]).'">'.$name.'</a>'
                        .'<div class="ls-user-email">'.$email.'</div>';
                })
                ->editColumn('username', '{{$username}} @if(empty($allow_login)) <span class="label bg-gray">@lang("lang_v1.login_not_allowed")</span>@endif')
                ->addColumn(
                    'role',
                    function ($row) {
                        return $this->moduleUtil->getUserRoleName($row->id);
                    }
                )
                ->addColumn('outlet', function ($row) use ($locations) {
                    $permitted = $row->permitted_locations();
                    if ($permitted === 'all') {
                        return 'All outlets';
                    }
                    if (! is_array($permitted) || empty($permitted)) {
                        return '—';
                    }
                    $names = [];
                    foreach ($permitted as $lid) {
                        if (isset($locations[$lid])) {
                            $names[] = $locations[$lid];
                        }
                    }

                    return e(implode(', ', $names) ?: '—');
                })
                ->addColumn('target_daily', function ($row) {
                    if (! $this->userIsSalesRepresentative($row)) {
                        return '—';
                    }

                    return $this->targetInputHtml($row->id, 'sales_target_daily', $row->sales_target_daily);
                })
                ->addColumn('target_weekly', function ($row) {
                    if (! $this->userIsSalesRepresentative($row)) {
                        return '—';
                    }

                    return $this->targetInputHtml($row->id, 'sales_target_weekly', $row->sales_target_weekly);
                })
                ->addColumn('target_monthly', function ($row) {
                    if (! $this->userIsSalesRepresentative($row)) {
                        return '—';
                    }

                    return $this->targetInputHtml($row->id, 'sales_target_monthly', $row->sales_target_monthly ?? $row->sales_target);
                })
                ->addColumn('last_active', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('M j, Y g:i A') : '—';
                })
                ->addColumn('enabled', function ($row) {
                    if ($this->userIsAdmin($row)) {
                        return '<label class="ls-switch ls-switch--lock" title="Admin stays active"><input type="checkbox" checked disabled><i></i></label>';
                    }
                    $on = $row->status === 'active' ? 'checked' : '';

                    return '<label class="ls-switch"><input type="checkbox" class="ls-user-enabled" data-id="'.$row->id.'" '.$on.'><i></i></label>';
                })
                ->addColumn('action', function ($row) use ($adminCount) {
                    $html = '';
                    if (auth()->user()->can('user.update') || auth()->user()->can('user.view')) {
                        $html .= '<a href="'.action([\App\Http\Controllers\ManageUserController::class, 'edit'], [$row->id]).'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary"><i class="glyphicon glyphicon-edit"></i> '.e(__('messages.edit')).'</a>&nbsp;';
                    }
                    $isLastAdmin = $this->userIsAdmin($row) && $adminCount <= 1;
                    if (auth()->user()->can('user.delete') && ! $isLastAdmin) {
                        $html .= '<button data-href="'.action([\App\Http\Controllers\ManageUserController::class, 'destroy'], [$row->id]).'" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_user_button"><i class="glyphicon glyphicon-trash"></i> '.e(__('messages.delete')).'</button>';
                    }

                    return $html;
                })
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('user_cell', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), ' ', COALESCE(email, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['action', 'username', 'user_cell', 'enabled', 'target_daily', 'target_weekly', 'target_monthly'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');
        $roles = $this->getRolesArray($business_id);
        $locations = BusinessLocation::where('business_id', $business_id)->Active()->get();

        return view('manage_user.index')->with(compact('roles', 'locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (! $this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
        }

        $roles = $this->getRolesArray($business_id);
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $locations = BusinessLocation::where('business_id', $business_id)
                                    ->Active()
                                    ->get();

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);

        return view('manage_user.create')
                ->with(compact('roles', 'username_ext', 'locations', 'form_partials'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (! empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            $request['cmmsn_percent'] = ! empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;

            $request['max_sales_discount_percent'] = ! is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;

            if ($this->requestAssignsAdminRole($request)) {
                $request->merge(['is_active' => 'active']);
            }

            $user = $this->moduleUtil->createUser($request);

            event(new UserCreatedOrModified($user, 'added'));

            $output = ['success' => 1,
                'msg' => __('user.user_added'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('users')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::where('business_id', $business_id)
                    ->with(['contactAccess', 'media', 'roles'])
                    ->findOrFail($id);

        $locations = BusinessLocation::where('business_id', $business_id)->get();
        $permitted_locations = $user->permitted_locations();
        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);
        $isLastAdmin = $this->isLastAdmin($user);
        $isSalesRep = $this->userIsSalesRepresentative($user);
        $salesStats = $isSalesRep ? $this->userSalesStats((int) $user->id, (int) $business_id) : [];
        $users = User::forDropdown($business_id, false);
        $activities = Activity::forSubject($user)->with(['causer', 'subject'])->latest()->get();
        $user->role_name = $this->moduleUtil->getUserRoleName($user->id);
        $user->ensureBarcodeSwitchId();

        return view('manage_user.show')
                ->with(compact('user', 'locations', 'permitted_locations', 'view_partials', 'isLastAdmin', 'isSalesRep', 'salesStats', 'users', 'activities'));
    }

    protected function userSalesStats(int $userId, int $businessId): array
    {
        $base = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereIn('status', ['final', 'completed'])
            ->where('created_by', $userId);

        $todayStart = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $key = $day->toDateString();
            $days[$key] = [
                'label' => $day->format('M j'),
                'total' => (float) (clone $base)->whereDate('transaction_date', $key)->sum('final_total'),
            ];
        }

        $todayTotal = (float) (clone $base)->whereDate('transaction_date', $todayStart->toDateString())->sum('final_total');
        $weekTotal = (float) (clone $base)->where('transaction_date', '>=', $weekStart)->sum('final_total');
        $monthTotal = (float) (clone $base)->where('transaction_date', '>=', $monthStart)->sum('final_total');

        $last7 = array_slice($days, -7, 7, true);
        $saleCount7 = 0;
        foreach ($last7 as $row) {
            if ($row['total'] > 0) {
                $saleCount7++;
            }
        }

        return [
            'today' => $todayTotal,
            'week' => $weekTotal,
            'month' => $monthTotal,
            'days' => array_values($days),
            'total_7' => array_sum(array_column($last7, 'total')),
            'avg_day_7' => count($last7) ? array_sum(array_column($last7, 'total')) / count($last7) : 0,
        ];
    }

    protected function formatSalesTarget($value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return number_format((float) $value, 2);
    }

    protected function targetInputHtml(int $userId, string $field, $value): string
    {
        $val = ($value === null || $value === '') ? '' : number_format((float) $value, 2, '.', '');
        $can = auth()->user()->can('user.update');
        $ro = $can ? '' : 'readonly';

        return '<label class="ls-target-in"><span>$</span><input type="text" class="ls-target-input" data-id="'.$userId.'" data-field="'.$field.'" value="'.e($val).'" placeholder="0.00" '.$ro.'></label>';
    }

    protected function userIsSalesRepresentative($user): bool
    {
        $names = $user instanceof User ? $user->getRoleNames() : collect();
        foreach ($names as $name) {
            $plain = trim(explode('#', (string) $name)[0]);
            if (strcasecmp($plain, 'Sales Representative') === 0) {
                return true;
            }
        }

        return false;
    }

    protected function userIsAdmin($user): bool
    {
        $names = $user instanceof User ? $user->getRoleNames() : collect();
        foreach ($names as $name) {
            $plain = strtolower(trim(explode('#', (string) $name)[0]));
            if ($plain === 'admin') {
                return true;
            }
        }

        return false;
    }

    protected function listedAdminCount(int $businessId): int
    {
        return User::where('business_id', $businessId)
            ->user()
            ->where('is_cmmsn_agnt', 0)
            ->whereHas('roles', function ($q) use ($businessId) {
                $q->where('roles.name', 'Admin#'.$businessId)
                    ->orWhere('roles.name', 'Admin');
            })
            ->count();
    }

    protected function isLastAdmin($user): bool
    {
        if (! $this->userIsAdmin($user)) {
            return false;
        }

        $count = $this->listedAdminCount((int) $user->business_id);

        return $count <= 1;
    }

    protected function requestAssignsAdminRole($request): bool
    {
        $roleId = $request->input('role');
        if (empty($roleId)) {
            return false;
        }
        $role = Role::find($roleId);
        if (! $role) {
            return false;
        }
        $plain = strtolower(trim(explode('#', (string) $role->name)[0]));

        return $plain === 'admin';
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::where('business_id', $business_id)
                    ->with(['contactAccess', 'media'])
                    ->findOrFail($id);

        $roles = $this->getRolesArray($business_id);

        $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();

        if ($user->status == 'active') {
            $is_checked_checkbox = true;
        } else {
            $is_checked_checkbox = false;
        }

        $locations = BusinessLocation::where('business_id', $business_id)
                                    ->get();

        $permitted_locations = $user->permitted_locations();
        $username_ext = $this->moduleUtil->getUsernameExtension();

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);
        $isLastAdmin = $this->isLastAdmin($user);
        $isSalesRep = $this->userIsSalesRepresentative($user);
        $salesStats = $isSalesRep ? $this->userSalesStats((int) $user->id, (int) $business_id) : [];
        $users = User::forDropdown($business_id, false);
        $activities = Activity::forSubject($user)->with(['causer', 'subject'])->latest()->get();

        $user->ensureBarcodeSwitchId();

        return view('manage_user.edit')
                ->with(compact('roles', 'user', 'contact_access', 'is_checked_checkbox', 'locations', 'permitted_locations', 'form_partials', 'username_ext', 'isLastAdmin', 'isSalesRep', 'salesStats', 'users', 'activities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Disable in demo
        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }
        
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

         //Check if subscribed
         if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        //Check for users quota if allow_login is true
        if (!empty($request->input('allow_login'))) {
            if (! $this->moduleUtil->isQuotaAvailable('users', $business_id)) {
                return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
            }
        }

        try {
            $user_data = $request->only(['surname', 'first_name', 'last_name', 'email', 'selected_contacts', 'marital_status',
                'blood_group', 'contact_number', 'fb_link', 'twitter_link', 'social_media_1',
                'social_media_2', 'permanent_address', 'current_address',
                'guardian_name', 'custom_field_1', 'custom_field_2',
                'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'cmmsn_percent', 'gender', 'max_sales_discount_percent', 'family_number', 'alt_number', 'is_enable_service_staff_pin']);

            $user_data['status'] = ! empty($request->input('is_active')) ? 'active' : 'inactive';

            $user_data['is_enable_service_staff_pin'] = ! empty($request->input('is_enable_service_staff_pin')) ? true : false;
            $user_data['barcode_login'] = ! empty($request->input('barcode_login')) ? 1 : 0;
            if (! empty($user_data['barcode_login'])) {
                $existingSwitch = User::where('business_id', $business_id)->find($id);
                $user_data['barcode_switch_id'] = $request->input('barcode_switch_id')
                    ?: ($existingSwitch->barcode_switch_id ?? null)
                    ?: User::makeBarcodeSwitchId();
            }

           

            if (! isset($user_data['selected_contacts'])) {
                $user_data['selected_contacts'] = 0;
            }

            if (empty($request->input('allow_login'))) {
                $user_data['username'] = null;
                $user_data['password'] = null;
                $user_data['allow_login'] = 0;
            } else {
                $user_data['allow_login'] = 1;
            }

            if (! empty($request->input('password'))) {
                $user_data['password'] = $user_data['allow_login'] == 1 ? Hash::make($request->input('password')) : null;
            }


            if (! empty($request->input('service_staff_pin'))) {
                $user_data['service_staff_pin'] = $request->input('service_staff_pin');
            }
            

            //Sales commission percentage
            $user_data['cmmsn_percent'] = ! empty($user_data['cmmsn_percent']) ? $this->moduleUtil->num_uf($user_data['cmmsn_percent']) : 0;

            $user_data['max_sales_discount_percent'] = ! is_null($user_data['max_sales_discount_percent']) ? $this->moduleUtil->num_uf($user_data['max_sales_discount_percent']) : null;

            if (! empty($request->input('dob'))) {
                $user_data['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            if (! empty($request->input('bank_details'))) {
                $user_data['bank_details'] = json_encode($request->input('bank_details'));
            }

            DB::beginTransaction();

            if ($user_data['allow_login'] && $request->has('username')) {
                $user_data['username'] = $request->input('username');
                $ref_count = $this->moduleUtil->setAndGetReferenceCount('username');
                if (blank($user_data['username'])) {
                    $user_data['username'] = $this->moduleUtil->generateReferenceNumber('username', $ref_count);
                }

                $username_ext = $this->moduleUtil->getUsernameExtension();
                if (! empty($username_ext)) {
                    $user_data['username'] .= $username_ext;
                }
            }

            $user = User::where('business_id', $business_id)
                          ->findOrFail($id);

            if ($this->userIsAdmin($user) || $this->requestAssignsAdminRole($request)) {
                $user_data['status'] = 'active';
            }

            $user->update($user_data);
            $role_id = $request->input('role');
            $user_role = $user->roles->first();
            $previous_role = ! empty($user_role->id) ? $user_role->id : 0;
            if ($previous_role != $role_id) {
                $is_admin = $this->moduleUtil->is_admin($user);
                $all_admins = $this->getAdmins();
                //If only one admin then can not change role
                if ($is_admin && count($all_admins) <= 1) {
                    throw new \Exception(__('lang_v1.cannot_change_role'));
                }
                if (! empty($previous_role)) {
                    $user->removeRole($user_role->name);
                }

                $role = Role::findOrFail($role_id);
                $user->assignRole($role->name);
            }

            //Grant Location permissions
            $this->moduleUtil->giveLocationPermissions($user, $request);

            //Assign selected contacts
            if ($user_data['selected_contacts'] == 1) {
                $contact_ids = $request->get('selected_contact_ids');
            } else {
                $contact_ids = [];
            }
            $user->contactAccess()->sync($contact_ids);

            //Update module fields for user
            $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_saved', 'model_instance' => $user]);

            $this->moduleUtil->activityLog($user, 'edited', null, ['name' => $user->user_full_name]);
           
            event(new UserCreatedOrModified($user, 'updated'));
            
            $output = ['success' => 1,
                'msg' => __('user.user_update_success'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect('users')->with('status', $output);
    }

    private function getAdmins()
    {
        $business_id = request()->session()->get('user.business_id');
        $admins = User::role('Admin#'.$business_id)->get();

        return $admins;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Disable in demo
        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        if (! auth()->user()->can('user.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $user = User::where('business_id', $business_id)
                    ->findOrFail($id);

                if ($this->isLastAdmin($user)) {
                    return ['success' => false, 'msg' => 'The last admin cannot be deleted.'];
                }

                $this->moduleUtil->activityLog($user, 'deleted', null, ['name' => $user->user_full_name, 'id' => $user->id]);

                $user->delete();
                event(new UserCreatedOrModified($user, 'deleted'));

                $output = ['success' => true,
                    'msg' => __('user.user_delete_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function toggleEnabled($user)
    {
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $id = $user instanceof User ? $user->id : $user;
        $business_id = request()->session()->get('user.business_id');
        $model = User::where('business_id', $business_id)->findOrFail($id);
        if ($this->userIsAdmin($model)) {
            $model->status = 'active';
            $model->save();

            return response()->json(['success' => false, 'msg' => 'Admin stays active.', 'status' => 'active'], 422);
        }
        $enabled = request()->boolean('enabled');
        $model->status = $enabled ? 'active' : 'inactive';
        $model->save();

        return response()->json([
            'success' => true,
            'status' => $model->status,
            'msg' => $enabled ? 'User enabled successfully.' : 'User disabled successfully.',
        ]);
    }

    public function updateSalesTargets($user)
    {
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $id = $user instanceof User ? $user->id : $user;
        $business_id = request()->session()->get('user.business_id');
        $model = User::where('business_id', $business_id)->findOrFail($id);
        if (! $this->userIsSalesRepresentative($model)) {
            return response()->json(['success' => false, 'msg' => 'Sales targets apply to Sales Representative users only.'], 422);
        }
        $allowed = ['sales_target_daily', 'sales_target_weekly', 'sales_target_monthly'];
        $field = (string) request()->input('field');
        if (! in_array($field, $allowed, true)) {
            return response()->json(['success' => false, 'msg' => 'Invalid field'], 422);
        }

        $raw = request()->input('value');
        $value = ($raw === null || $raw === '') ? null : $this->moduleUtil->num_uf($raw);
        $model->{$field} = $value;
        if ($field === 'sales_target_monthly') {
            $model->sales_target = $value;
        }
        $model->save();

        return response()->json(['success' => true]);
    }

    /**
     * Retrives roles array (Hides admin role from non admin users)
     *
     * @param  int  $business_id
     * @return array $roles
     */
    private function getRolesArray($business_id)
    {
        $business_id = request()->session()->get('user.business_id');
        (new BusinessUtil())->createDefaultSalesRole((int) $business_id);

        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        foreach ($roles_array as $key => $value) {
            if (! $is_admin && $value == 'Admin#'.$business_id) {
                continue;
            }
            $roles[$key] = str_replace('#'.$business_id, '', $value);
        }

        return $roles;
    }

    /**
     * Signes in from user id
     *
     * @param  int  $id
     */
    public function signInAsUser($id)
    {
        if (! auth()->user()->can('superadmin') && empty(session('previous_user_id'))) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = auth()->user()->id;
        $username = auth()->user()->username;
        session()->flush();

        if (request()->has('save_current')) {
            session(['previous_user_id' => $user_id, 'previous_username' => $username]);
        }

        Auth::loginUsingId($id);

        return redirect()->route('home');
    }

    /**
     * Dynamic Code128 PNG for a user username / quick-switch ID.
     */
    public function barcodeImage(Request $request)
    {
        if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.create') && ! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $text = trim((string) $request->get('text', ''));
        if ($text === '' || strlen($text) > 80) {
            abort(422);
        }

        try {
            $png = \DNS1D::getBarcodePNG($text, 'C128', 2, 70, [0, 0, 0], false);
        } catch (\Throwable $e) {
            abort(422);
        }

        return response(base64_decode($png))
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store');
    }
}
