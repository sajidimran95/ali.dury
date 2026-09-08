@php
    $stDaily = isset($user) ? ($user->sales_target_daily ?? null) : null;
    $stWeekly = isset($user) ? ($user->sales_target_weekly ?? null) : null;
    $stMonthly = isset($user) ? ($user->sales_target_monthly ?? $user->sales_target ?? null) : null;
@endphp
@component('components.widget', ['title' => 'Sales targets'])
    <p class="help-block" style="margin:0 15px 12px;">Set daily, weekly and monthly targets. Leave blank if not used.</p>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('sales_target_daily', 'Daily') !!}
            {!! Form::text('sales_target_daily', $stDaily, ['class' => 'form-control input_number', 'placeholder' => '0.00']); !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('sales_target_weekly', 'Weekly') !!}
            {!! Form::text('sales_target_weekly', $stWeekly, ['class' => 'form-control input_number', 'placeholder' => '0.00']); !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('sales_target_monthly', 'Monthly') !!}
            {!! Form::text('sales_target_monthly', $stMonthly, ['class' => 'form-control input_number', 'placeholder' => '0.00']); !!}
        </div>
    </div>
@endcomponent
