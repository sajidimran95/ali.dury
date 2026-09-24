@php
	$subtype = '';
@endphp
@if(!empty($transaction_sub_type))
	@php
		$subtype = '?sub_type='.$transaction_sub_type;
	@endphp
@endif

@if(!empty($transactions))
	<table class="table">
		@foreach ($transactions as $transaction)
			@php
				// Prefer business name when personal name is empty (many B2B customers store only supplier_business_name)
				$c = $transaction->contact;
				$customerLabel = '';
				if (!empty($c)) {
					$customerLabel = trim((string) ($c->supplier_business_name ?? ''));
					if ($customerLabel === '') {
						$customerLabel = trim((string) ($c->name ?? ''));
					}
					if ($customerLabel === '') {
						$customerLabel = trim((string) ($c->contact_id ?? ''));
					}
				}
				if ($customerLabel === '') {
					$customerLabel = __('lang_v1.walk_in_customer');
				}
			@endphp
			<tr class="cursor-pointer"
	    		title="Customer: {{ $customerLabel }}@if(!empty($transaction->contact->mobile) && $transaction->contact->is_default == 0)&#10;Mobile: {{ $transaction->contact->mobile }}@endif">
				<td>
					{{ $loop->iteration}}.
				</td>
				<td class="pos-recent-date text-nowrap">
					{{ @format_datetime($transaction->transaction_date) }}
				</td>
				<td class="col-md-4">
					{{ $transaction->invoice_no }} ({{ $customerLabel }})
					@if(!empty($transaction->table))
						- {{$transaction->table->name}}
					@endif
				</td>
				<td class="display_currency col-md-2">
					{{ $transaction->final_total }}
				</td>
				<td class="pos-recent-actions-cell">
					<div class="pos-recent-actions">
					@if(auth()->user()->can('sell.update') || auth()->user()->can('direct_sell.update'))
					<a href="{{ route('pos.edit', ['po' => $transaction->id]) . $subtype }}" class="pos-recent-act pos-recent-act--edit" title="@lang('messages.edit')">
	    				<i class="fas fa-pen" aria-hidden="true"></i>
	    			</a>
	    			@endif

					@if(!auth()->user()->can('sell.update') && auth()->user()->can('edit_pos_payment'))
						<a href="{{route('edit-pos-payment', ['id' => $transaction->id])}}"
						title="@lang('lang_v1.add_edit_payment')" class="pos-recent-act pos-recent-act--edit">
						    <i class="fas fa-money-bill-alt" aria-hidden="true"></i>
						</a>
					@endif

	    			<a href="{{action([\App\Http\Controllers\SellPosController::class, 'printInvoice'], [$transaction->id])}}" class="print-invoice-link pos-recent-act pos-recent-act--print" title="@lang('messages.print')">
	    				<i class="fa fa-print" aria-hidden="true"></i>
	    			</a>

					@if($transaction->status == 'final' && (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return')))
					<a href="{{ action([\App\Http\Controllers\SellReturnController::class, 'add'], [$transaction->id]) }}" class="pos-recent-act pos-recent-act--return" title="@lang('lang_v1.sell_return')">
						<i class="fas fa-undo" aria-hidden="true"></i>
					</a>
					@endif

                    @if(auth()->user()->can('sell.delete') || auth()->user()->can('direct_sell.delete'))
	    			    <a href="{{action([\App\Http\Controllers\SellPosController::class, 'destroy'], [$transaction->id])}}" class="delete-sale pos-recent-act pos-recent-act--delete" title="@lang('messages.delete')">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
	    			@endif
					</div>
				</td>
			</tr>
		@endforeach
	</table>
@else
	<p>@lang('sale.no_recent_transactions')</p>
@endif
