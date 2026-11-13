@extends('layouts.app')
@section('title', __('lang_v1.bulk_pricing'))

@section('content')
<section class="content-header">
    <h1>@lang('lang_v1.bulk_pricing')</h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-md-12">
                <form id="bulk_pricing_filter_form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('category_id', __('product.category') . ':') !!}
                                {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all'), 'style' => 'width:100%']) !!}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                                {!! Form::select('brand_id', $brands, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all'), 'style' => 'width:100%']) !!}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('product_type', __('product.product_type') . ':') !!}
                                {!! Form::select('product_type', ['single' => __('lang_v1.single'), 'variable' => __('lang_v1.variable')], null, ['class' => 'form-control select2', 'placeholder' => __('messages.all'), 'style' => 'width:100%']) !!}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white btn-block" id="filter_products">
                                    <i class="fa fa-filter"></i> @lang('messages.filter')
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-success', 'title' => __('lang_v1.update_prices')])
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info" style="margin-bottom:12px; padding:10px 14px;">
                    <i class="fa fa-check-square-o"></i>
                    <strong><span class="bulk-selected-count">0</span></strong> @lang('lang_v1.products_selected')
                </div>
                <p class="help-block">@lang('lang_v1.bulk_pricing_help')</p>
                <p class="text-info"><i class="fa fa-info-circle"></i> @lang('lang_v1.bulk_pricing_tax_note')</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('update_type', __('lang_v1.update_type') . ':*') !!}
                    <select class="form-control" id="update_type" required>
                        <option value="set">@lang('lang_v1.set_same_price')</option>
                        <option value="percentage">@lang('lang_v1.percentage')</option>
                        <option value="flat">@lang('lang_v1.flat_amount')</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('update_value', __('lang_v1.value') . ':*') !!}
                    <input type="number" step="0.01" min="0" class="form-control" id="update_value" placeholder="e.g., 20" required>
                    <span class="help-block">
                        <small id="update_value_help">@lang('lang_v1.set_price_help_text')</small>
                    </span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="button" class="tw-dw-btn tw-dw-btn-success tw-text-white btn-block" id="apply_bulk_update">
                        <i class="fa fa-check"></i> @lang('lang_v1.apply_update')
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('notes', __('lang_v1.notes') . ':') !!}
                    <textarea class="form-control" id="notes" rows="2" placeholder="@lang('lang_v1.bulk_price_notes_placeholder')"></textarea>
                </div>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.select_products')])
        <div class="row" style="margin-bottom:8px;">
            <div class="col-md-12">
                <p class="text-info" style="margin:0;">
                    <i class="fa fa-check-square-o"></i>
                    <strong><span class="bulk-selected-count">0</span></strong> @lang('lang_v1.products_selected')
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="bulk_pricing_table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select_all_products"></th>
                                <th>@lang('sale.product')</th>
                                <th>@lang('product.sku')</th>
                                <th>@lang('product.brand')</th>
                                <th>@lang('product.category')</th>
                                <th>@lang('lang_v1.price_exc_tax')</th>
                                <th>@lang('lang_v1.price_inc_tax')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    @endcomponent

    <div class="row">
        <div class="col-md-12 text-center">
            <a href="{{ action([\App\Http\Controllers\BulkPricingController::class, 'history']) }}" class="tw-dw-btn tw-dw-btn-neutral">
                <i class="fa fa-history"></i> @lang('lang_v1.view_bulk_pricing_history')
            </a>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        // Keep selections across search / page / filter reloads
        var selectedIds = {};

        function selectedList() {
            return Object.keys(selectedIds);
        }

        function updateSelectedCount() {
            $('.bulk-selected-count').text(selectedList().length);
        }

        function restoreSelectedCheckboxes() {
            $('.row-select-bulk-price').each(function() {
                var id = String($(this).val());
                $(this).prop('checked', !!selectedIds[id]);
            });
            var $visible = $('.row-select-bulk-price');
            var allChecked = $visible.length > 0 && $visible.filter(':checked').length === $visible.length;
            $('#select_all_products').prop('checked', allChecked);
            updateSelectedCount();
        }

        function clearSelected() {
            selectedIds = {};
            $('.row-select-bulk-price').prop('checked', false);
            $('#select_all_products').prop('checked', false);
            updateSelectedCount();
        }

        var bulk_pricing_table = $('#bulk_pricing_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ action([\App\Http\Controllers\BulkPricingController::class, 'getProducts']) }}',
                data: function(d) {
                    d.category_id = $('#category_id').val();
                    d.brand_id = $('#brand_id').val();
                    d.product_type = $('#product_type').val();
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'product_name', name: 'products.name' },
                { data: 'sub_sku', name: 'v.sub_sku' },
                { data: 'brand', name: 'brands.name' },
                { data: 'category', name: 'c1.name' },
                { data: 'default_sell_price', name: 'v.default_sell_price', searchable: false },
                { data: 'sell_price_inc_tax', name: 'v.sell_price_inc_tax', searchable: false }
            ],
            columnDefs: [{
                targets: 0,
                orderable: false,
                searchable: false
            }],
            drawCallback: function() {
                restoreSelectedCheckboxes();
            }
        });

        $('#filter_products').click(function() {
            bulk_pricing_table.ajax.reload();
        });

        $('#select_all_products').on('change', function() {
            var checked = $(this).is(':checked');
            $('.row-select-bulk-price').each(function() {
                var id = String($(this).val());
                $(this).prop('checked', checked);
                if (checked) {
                    selectedIds[id] = true;
                } else {
                    delete selectedIds[id];
                }
            });
            updateSelectedCount();
        });

        $(document).on('change', '.row-select-bulk-price', function() {
            var id = String($(this).val());
            if ($(this).is(':checked')) {
                selectedIds[id] = true;
            } else {
                delete selectedIds[id];
                $('#select_all_products').prop('checked', false);
            }
            updateSelectedCount();
        });

        $('#update_type').on('change', function() {
            var type = $(this).val();
            var help = '@lang('lang_v1.set_price_help_text')';
            var placeholder = 'e.g., 20';
            if (type === 'percentage') {
                help = '@lang('lang_v1.percentage_help_text')';
                placeholder = 'e.g., 10 or -5';
            } else if (type === 'flat') {
                help = '@lang('lang_v1.flat_amount_help_text')';
                placeholder = 'e.g., 5 or -2';
            }
            $('#update_value_help').text(help);
            $('#update_value').attr('placeholder', placeholder);
            if (type === 'set') {
                $('#update_value').attr('min', '0');
            } else {
                $('#update_value').removeAttr('min');
            }
        }).trigger('change');

        $('#apply_bulk_update').click(function() {
            var selected = selectedList();

            if (selected.length === 0) {
                swal('@lang('lang_v1.please_select_products')');
                return;
            }

            var update_value = parseFloat($('#update_value').val());
            if (isNaN(update_value)) {
                swal('@lang('lang_v1.please_enter_valid_value')');
                return;
            }

            swal({
                title: '@lang('messages.sure')',
                text: '@lang('lang_v1.bulk_price_update_confirm', ['count' => ''])' + selected.length + ' @lang('lang_v1.products_lowercase')?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    var $btn = $(this);
                    $btn.attr('disabled', true);

                    $.ajax({
                        url: '{{ action([\App\Http\Controllers\BulkPricingController::class, 'update']) }}',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            variation_ids: selected,
                            update_type: $('#update_type').val(),
                            update_value: update_value,
                            price_field: 'default_sell_price',
                            notes: $('#notes').val(),
                            category_id: $('#category_id').val(),
                            brand_id: $('#brand_id').val(),
                            product_type: $('#product_type').val()
                        },
                        success: function(result) {
                            if (result.success) {
                                swal('Success', result.msg, 'success');
                                clearSelected();
                                bulk_pricing_table.ajax.reload();
                                $('#update_value').val('');
                                $('#notes').val('');
                            } else {
                                swal('Error', result.msg, 'error');
                            }
                        },
                        error: function() {
                            swal('Error', '@lang('messages.something_went_wrong')', 'error');
                        },
                        complete: function() {
                            $btn.attr('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
