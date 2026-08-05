<?php

namespace App\Http\Controllers;

use App\Product;
use App\Warranty;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WarrantyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $warranties = Warranty::where('business_id', $business_id)
                         ->select(['id', 'name', 'description', 'duration', 'duration_type']);

            return Datatables::of($warranties)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\WarrantyController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>'
                 )
                 ->removeColumn('id')
                 ->editColumn('duration', function ($row) {
                     return $row->duration.' '.__('lang_v1.'.$row->duration_type);
                 })
                 ->rawColumns(['action'])
                 ->make(true);
        }

        $business_id = (int) $business_id;
        $warranty_stats = $this->getWarrantyListStats($business_id);

        return view('warranties.index')->with(compact('warranty_stats'));
    }

    /**
     * KPI stats for warranties list cards.
     */
    protected function getWarrantyListStats(int $businessId): array
    {
        $total = (int) Warranty::where('business_id', $businessId)->count();
        $days = (int) Warranty::where('business_id', $businessId)->where('duration_type', 'days')->count();
        $months = (int) Warranty::where('business_id', $businessId)->where('duration_type', 'months')->count();
        $years = (int) Warranty::where('business_id', $businessId)->where('duration_type', 'years')->count();
        $productsWith = (int) Product::where('business_id', $businessId)
            ->where('type', '!=', 'modifier')
            ->whereNotNull('warranty_id')
            ->where('warranty_id', '>', 0)
            ->count();

        return [
            'total_warranties' => $total,
            'duration_days' => $days,
            'duration_months' => $months,
            'duration_years' => $years,
            'products_with_warranty' => $productsWith,
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('warranties.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'description', 'duration', 'duration_type']);
            $input['business_id'] = $business_id;

            $status = Warranty::create($input);

            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function show(Warranty $warranty)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $warranty = Warranty::where('business_id', $business_id)->find($id);

            return view('warranties.edit')
                ->with(compact('warranty'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description', 'duration', 'duration_type']);

                $warranty = Warranty::where('business_id', $business_id)->findOrFail($id);

                $warranty->update($input);

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warranty $warranty)
    {
        //
    }
}
