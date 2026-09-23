@extends('layouts.app')
@section('title', __('lang_v1.bulk_pricing_history'))

@section('content')
<section class="content-header">
    <h1>@lang('lang_v1.bulk_pricing_history')</h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="bulk_pricing_history_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.date')</th>
                                <th>@lang('business.user')</th>
                                <th>@lang('lang_v1.update_type')</th>
                                <th>@lang('lang_v1.value')</th>
                                <th>@lang('lang_v1.products_count')</th>
                                <th>@lang('lang_v1.notes')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <a href="{{ action([\App\Http\Controllers\BulkPricingController::class, 'index']) }}" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    @endcomponent>
</section>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $('#bulk_pricing_history_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ action([\App\Http\Controllers\BulkPricingController::class, 'history']) }}',
            order: [[0, 'desc']],
            columns: [
                { data: 'created_at' },
                { data: 'user' },
                { data: 'update_type' },
                { data: 'update_value' },
                { data: 'products_count' },
                { data: 'notes' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });

    // View updated products details
    $(document).on('click', '.view-bulk-update', function(e) {
        e.preventDefault();
        var update_id = $(this).data('id');
        
        $.ajax({
            url: '{{ url("bulk-pricing/details") }}/' + update_id,
            method: 'GET',
            success: function(response) {
                $('#bulk_update_details_modal').modal('show');
                $('#update_details_content').html(response);
            },
            error: function() {
                toastr.error('Error loading details');
            }
        });
    });
</script>

<!-- Modal for viewing update details -->
<div class="modal fade" id="bulk_update_details_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">@lang('lang_v1.bulk_update_details')</h4>
            </div>
            <div class="modal-body" id="update_details_content">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>
@endsection
