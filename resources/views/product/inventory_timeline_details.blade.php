@php
	$last_date_label = null;

	//@format_date/@format_time are blade directives, not real functions, so
	//they can't be called from within a @php block - build the same output
	//manually instead.
	$__time_format = session('business.time_format') == 24 ? 'H:i' : 'h:i A';
	$__date_format = session('business.date_format');
@endphp
<div class="row">
	<div class="col-md-12">
		<h4>{{$stock_details['variation']}}</h4>
		<p class="text-muted">
			@lang('report.current_stock'):
			<strong><span class="display_currency" data-is_quantity="true">{{$stock_details['current_stock']}}</span> {{$stock_details['unit']}}</strong>
		</p>
		<hr>

		@if(count($stock_history) > 0)
			<div style="position: relative; margin-left: 6px; padding-left: 22px; border-left: 2px solid #eee;">
				@foreach($stock_history as $history)
					@php
						$__ts = \Carbon\Carbon::createFromTimestamp(strtotime($history['date']));
						$date_label = $__ts->format($__date_format);
						$time_label = $__ts->format($__time_format);
						$is_positive = $history['quantity_change'] > 0;
						$dot_color = $is_positive ? '#00a65a' : '#dd4b39';

						$__contact = $history['contact_name'] ?? '';
						if (! empty($__contact) && ! empty($history['supplier_business_name'])) {
							$__contact .= ' - '.$history['supplier_business_name'];
						}

						$__ref_no = $history['ref_no'] ?? '';
						$ref_label = in_array($history['type'], ['sell', 'sell_transfer']) ? __('sale.invoice_no') : __('purchase.ref_no');
					@endphp
					@if($date_label !== $last_date_label)
						@php($last_date_label = $date_label)
						<div style="margin: 18px 0 8px -22px; font-weight: 600; color: #777;">{{$date_label}}</div>
					@endif
					<div style="position: relative; margin-bottom: 12px;">
						<span style="position: absolute; left: -28px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: {{$dot_color}};"></span>

						<span class="text-muted">{{$time_label}}</span>
						&mdash;
						<strong>{{$history['type_label']}}</strong>
						@if($is_positive)
							<span class="text-success">+<span class="display_currency" data-is_quantity="true" data-orig-value="{{$history['quantity_change']}}">{{$history['quantity_change']}}</span></span>
						@else
							<span class="text-danger"><span class="display_currency" data-is_quantity="true" data-orig-value="{{$history['quantity_change']}}">{{$history['quantity_change']}}</span></span>
						@endif
						{{$stock_details['unit']}}
						<span class="text-muted">&rarr; <span class="display_currency" data-is_quantity="true" data-orig-value="{{$history['stock']}}">{{$history['stock']}}</span> {{$stock_details['unit']}}</span>

						@if(!empty($__ref_no))
							<br>
							<small class="text-muted">{{$ref_label}}: {{$__ref_no}}</small>
						@endif
						@if(!empty($__contact))
							<br>
							<small class="text-muted">{{$__contact}}</small>
						@endif
					</div>
				@endforeach
			</div>
		@else
			<p class="text-center text-muted">@lang('lang_v1.no_stock_history_found')</p>
		@endif
	</div>
</div>
