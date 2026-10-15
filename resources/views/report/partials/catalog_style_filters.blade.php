@php
    $csfFormId = $csfFormId ?? 'catalog_style_form';
    $csfDateId = $csfDateId ?? 'spr_date_filter';
    $csfTitle = $csfTitle ?? '';
    $csfHasSort = ! empty($csfHasSort);
    $csfTableSelector = $csfTableSelector ?? '';
    $csfColumns = $csfColumns ?? [];
    $csfFilters = $csfFilters ?? [];
@endphp
<style>
    .mr-filter-list { max-height: 330px; overflow-y: auto; margin-bottom: 0; }
    .mr-filter-list .list-group-item { padding: 4px 10px; font-size: 13px; }
    .mr-check-list { max-height: 260px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 4px; padding: 4px 10px; margin-bottom: 6px; }
    #mr_choices tbody tr { cursor: pointer; }
    #mr_choices tbody tr.info > td { background-color: #dbeafe !important; font-weight: 600; }
    .daterangepicker select.monthselect,
    .daterangepicker select.yearselect {
        color-scheme: light;
        background: #fff;
        color: #111827;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 2px 4px;
        height: auto;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
</style>

<div id="catalog_style_filters" data-form="{{ $csfFormId }}" data-date-id="{{ $csfDateId }}" data-table="{{ $csfTableSelector }}" data-reload="{{ $csfReload ?? '' }}">
    {!! Form::open(['url' => '#', 'method' => 'get', 'id' => $csfFormId]) !!}
        <input type="text" name="date_range" class="hide" id="{{ $csfDateId }}" readonly autocomplete="off">
        <input type="hidden" name="start_date" class="csf-start">
        <input type="hidden" name="end_date" class="csf-end">

        <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-2">
            <button type="button" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-sm" data-toggle="modal" data-target="#modify_report_modal">
                <i class="fa fa-sliders-h"></i> Customize Report
            </button>
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" id="csf_refresh">
                <i class="fa fa-sync"></i> Refresh
            </button>
        </div>

        <div class="tw-flex tw-flex-wrap tw-items-end tw-gap-3 tw-mb-2">
            <div>
                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-600">Dates</label>
                <select class="form-control input-sm rd-preset" data-selected="this_month_to_date" style="min-width: 200px;"></select>
            </div>
            <div>
                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-600">From</label>
                <div class="input-group input-group-sm" style="width: 160px;">
                    <input type="text" class="form-control rd-from" autocomplete="off">
                    <span class="input-group-addon rd-open" style="cursor: pointer;"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
            <div>
                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-600">To</label>
                <div class="input-group input-group-sm" style="width: 160px;">
                    <input type="text" class="form-control rd-to" autocomplete="off">
                    <span class="input-group-addon rd-open" style="cursor: pointer;"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
            @if ($csfHasSort)
                <div>
                    <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-600">Sort By</label>
                    <select class="form-control input-sm rd-sort" style="min-width: 140px;">
                        @foreach (['default' => 'Default', 'date' => 'Date', 'num' => 'Num', 'name' => 'Name', 'item' => 'Item', 'amount' => 'Amount'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-4 tw-mb-2 tw-text-sm">
            <span class="tw-font-semibold tw-text-gray-600">Report Basis:</span>
            <label class="radio-inline tw-m-0"><input type="radio" class="rd-basis" name="csf_basis_bar" value="accrual" checked> Accrual</label>
            <label class="radio-inline tw-m-0"><input type="radio" class="rd-basis" name="csf_basis_bar" value="cash"> Cash</label>
            <a href="#" id="toggle_filters">Show Filters</a>
        </div>
        <div id="filter_chips" class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-3 tw-text-xs hide"></div>

        <div class="modal fade" id="modify_report_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Modify Report: {{ $csfTitle }}</h4>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#mr_display" data-toggle="tab">Display</a></li>
                            <li><a href="#mr_filters" data-toggle="tab">Filters</a></li>
                        </ul>
                        <div class="tab-content tw-pt-4">
                            <div class="tab-pane active" id="mr_display">
                                <fieldset class="tw-mb-4">
                                    <legend class="tw-text-xs tw-font-semibold tw-uppercase tw-text-gray-500">Report Date Range</legend>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label>Dates</label>
                                            <select class="form-control rd-preset"></select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>From</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control rd-from" autocomplete="off">
                                                <span class="input-group-addon rd-open" style="cursor: pointer;"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>To</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control rd-to" autocomplete="off">
                                                <span class="input-group-addon rd-open" style="cursor: pointer;"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="tw-mb-4">
                                    <legend class="tw-text-xs tw-font-semibold tw-uppercase tw-text-gray-500">Report Basis</legend>
                                    <label class="radio-inline"><input type="radio" name="basis" class="rd-basis-modal" value="accrual" checked> Accrual</label>
                                    <label class="radio-inline"><input type="radio" name="basis" class="rd-basis-modal" value="cash"> Cash</label>
                                    <p class="help-block">Accrual includes every invoice. Cash includes only invoices that are fully paid.</p>
                                </fieldset>
                                @if (! empty($csfColumns))
                                    <fieldset>
                                        <legend class="tw-text-xs tw-font-semibold tw-uppercase tw-text-gray-500">Columns</legend>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control input-sm tw-mb-2 mr-search" placeholder="Search Columns" data-list="#mr_column_list">
                                                <div id="mr_column_list" class="mr-check-list">
                                                    @foreach ($csfColumns as $index => $column)
                                                        <div class="checkbox tw-my-1"><label><input type="checkbox" class="mr-column" value="{{ $index }}" checked> {{ $column === '' ? '(left margin)' : $column }}</label></div>
                                                    @endforeach
                                                </div>
                                                <p class="help-block">Put a check mark next to each column that you want to appear in the report.</p>
                                            </div>
                                            @if ($csfHasSort)
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Sort by</label>
                                                        <select name="sort_by" class="form-control">
                                                            @foreach (['default' => 'Default', 'date' => 'Date', 'num' => 'Num', 'name' => 'Name', 'item' => 'Item', 'amount' => 'Amount'] as $value => $label)
                                                                <option value="{{ $value }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <label>Sort in</label>
                                                    <div class="radio tw-mt-0"><label><input type="radio" name="sort" value="asc"> Ascending order</label></div>
                                                    <div class="radio"><label><input type="radio" name="sort" value="desc" checked> Descending order</label></div>
                                                </div>
                                            @endif
                                        </div>
                                    </fieldset>
                                @endif
                            </div>
                            <div class="tab-pane" id="mr_filters">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <label class="tw-text-xs tw-uppercase tw-text-gray-500">Choose Filter</label>
                                        <input type="text" class="form-control input-sm tw-mb-1" id="mr_filter_search" placeholder="Search filters">
                                        <div class="list-group mr-filter-list">
                                            @foreach ($csfFilters as $key => $field)
                                                <a href="#mr_f_{{ $key }}" class="list-group-item @if ($loop->first) active @endif" data-toggle="tab" data-key="{{ $key }}">{{ $field['label'] }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-sm-5 tab-content">
                                        @foreach ($csfFilters as $key => $field)
                                            <div class="tab-pane @if ($loop->first) active @endif" id="mr_f_{{ $key }}" data-key="{{ $key }}" data-all="{{ $field['all'] ?? 'All' }}">
                                                <label>{{ $field['label'] }}</label>
                                                @if (($field['type'] ?? '') === 'date')
                                                    <select class="form-control tw-mb-2 rd-preset"></select>
                                                    <div class="row">
                                                        <div class="col-xs-6">
                                                            <small class="text-muted">From</small>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control rd-from" autocomplete="off">
                                                                <span class="input-group-addon rd-open" style="cursor: pointer;"><i class="fa fa-calendar"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-6">
                                                            <small class="text-muted">To</small>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control rd-to" autocomplete="off">
                                                                <span class="input-group-addon rd-open" style="cursor: pointer;"><i class="fa fa-calendar"></i></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p class="help-block tw-mt-2">Same as the report date range on the Display tab.</p>
                                                @else
                                                    {!! $field['html'] !!}
                                                    @if (! empty($field['help']))
                                                        <p class="help-block tw-mt-2">{{ $field['help'] }}</p>
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="tw-text-xs tw-uppercase tw-text-gray-500">Current Filter Choices</label>
                                        <table class="table table-bordered table-condensed tw-text-xs" id="mr_choices">
                                            <thead><tr><th>Filter</th><th>Set To</th></tr></thead>
                                            <tbody>
                                                @foreach ($csfFilters as $key => $field)
                                                    <tr data-key="{{ $key }}"><td>{{ $field['label'] }}</td><td class="mr-value">{{ $field['all'] ?? 'All' }}</td></tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-default btn-sm btn-block" id="mr_remove_selected">Remove Selected Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" id="mr_revert">Revert</button>
                        <button type="button" class="btn btn-primary" id="mr_ok">OK</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
</div>
