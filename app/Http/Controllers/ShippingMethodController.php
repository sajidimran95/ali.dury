<?php

namespace App\Http\Controllers;

use App\ShippingMethod;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ShippingMethodController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('shipping_method.view') && ! auth()->user()->can('shipping_method.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $methods = ShippingMethod::where('business_id', $business_id)
                ->select(['id', 'name', 'description', 'is_active']);

            return Datatables::of($methods)
                ->addColumn('status', function ($row) {
                    return $row->is_active
                        ? '<span class="label label-success">'.__('business.is_active').'</span>'
                        : '<span class="label label-default">'.__('lang_v1.inactive').'</span>';
                })
                ->addColumn(
                    'action',
                    '@can("shipping_method.update")
                    <button data-href="{{action([\App\Http\Controllers\ShippingMethodController::class, \'edit\'], [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit_shipping_method_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    @endcan
                    @can("shipping_method.delete")
                    <button data-href="{{action([\App\Http\Controllers\ShippingMethodController::class, \'destroy\'], [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_shipping_method_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('shipping_method.index');
    }

    public function create()
    {
        if (! auth()->user()->can('shipping_method.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('shipping_method.create');
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('shipping_method.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');
            $input['is_active'] = $request->boolean('is_active', true) ? 1 : 0;
            $input['name'] = trim((string) $input['name']);

            if ($input['name'] === '') {
                throw new \Exception(__('messages.something_went_wrong'));
            }

            $method = ShippingMethod::create($input);

            $output = [
                'success' => true,
                'data' => $method,
                'msg' => __('lang_v1.shipping_method_added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function edit($id)
    {
        if (! auth()->user()->can('shipping_method.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $shipping_method = ShippingMethod::where('business_id', $business_id)->findOrFail($id);

        return view('shipping_method.edit')->with(compact('shipping_method'));
    }

    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('shipping_method.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $shipping_method = ShippingMethod::where('business_id', $business_id)->findOrFail($id);

            $shipping_method->name = trim((string) $request->input('name'));
            $shipping_method->description = $request->input('description');
            $shipping_method->is_active = $request->boolean('is_active') ? 1 : 0;
            $shipping_method->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.shipping_method_updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('shipping_method.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $shipping_method = ShippingMethod::where('business_id', $business_id)->findOrFail($id);
            $shipping_method->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.shipping_method_deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
