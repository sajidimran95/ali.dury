@php
	$subtype = !empty($transaction_sub_type) ? '?sub_type=' . $transaction_sub_type : '';
	$avatar_colors = ['#f0ad4e', '#5bc0de', '#d9534f', '#5cb85c', '#9b59b6', '#e67e22', '#3498db', '#1abc9c'];
@endphp
<div class="pos-retrieve-sheet" role="dialog" aria-label="Retrieve sale">
	<div class="pos-retrieve-sheet-head">
		<span class="pos-retrieve-col-label">Parked sale</span>
		<span class="pos-retrieve-col-label pos-retrieve-col-label--cust">Customer</span>
	</div>
	<div class="pos-retrieve-sheet-body">
		@forelse($sales as $sale)
			@if($sale->is_suspend)
				@php
					$lines = $sale->sell_lines ?? collect();
					$preview_lines = $lines->take(2);
					$extra_count = max(0, $lines->count() - 2);
					$thumbs = [];
					$cart_lines = [];
					foreach ($lines as $line) {
						if (!empty($line->parent_sell_line_id)) {
							continue;
						}
						if (!empty($line->variation_id)) {
							$cart_lines[] = [
								'variation_id' => (int) $line->variation_id,
								'quantity' => (float) $line->quantity,
							];
						}
					}
					foreach ($lines->take(4) as $line) {
						$img = null;
						if (!empty($line->product) && $line->product->media && $line->product->media->count() > 0) {
							$img = $line->product->media->first()->display_url;
						} elseif (!empty($line->product) && !empty($line->product->product_image)) {
							$img = asset('/uploads/img/' . rawurlencode($line->product->product_image));
						}
						$thumbs[] = $img;
					}
					$cust_name = $sale->name ?: (optional($sale->contact)->name ?? 'Walk-In Customer');
					$cust_parts = preg_split('/\s+/', trim($cust_name));
					$initials = '';
					foreach (array_slice($cust_parts, 0, 2) as $part) {
						$initials .= mb_strtoupper(mb_substr($part, 0, 1));
					}
					if ($initials === '') {
						$initials = '?';
					}
					$color = $avatar_colors[crc32($cust_name) % count($avatar_colors)];
					$parked_by = trim($sale->added_by ?? '')
						?: (optional($sale->sales_person)->user_full_name
						?? optional($sale->sales_person)->first_name
						?? '');
					$parked_ago = '';
					if (!empty($sale->transaction_date)) {
						$parked_ago = \Carbon\Carbon::parse($sale->transaction_date)->diffForHumans();
					}
					$mobile = $sale->mobile ?? optional($sale->contact)->mobile;
					$email = optional($sale->contact)->email;
					$edit_url = url('/pos/' . $sale->id . '/edit') . $subtype;
					$contact_id = $sale->contact_id ?? optional($sale->contact)->id;
				@endphp
				<div class="pos-retrieve-row is-clickable"
					role="link"
					tabindex="0"
					data-href="{{ $edit_url }}"
					data-transaction-id="{{ $sale->id }}"
					data-contact-id="{{ $contact_id }}"
					data-contact-name="{{ $cust_name }}"
					data-lines="{{ e(json_encode($cart_lines)) }}"
					onclick="if (window.posOpenRetrievedSaleRow) { window.posOpenRetrievedSaleRow(event, this); } else if (!event.target.closest('a')) { window.location.href = this.getAttribute('data-href'); }">
					<div class="pos-retrieve-sale">
						<div class="pos-retrieve-thumbs">
							@forelse($thumbs as $idx => $thumb)
								@if($idx === 3 && $lines->count() > 4)
									<span class="pos-retrieve-thumb pos-retrieve-thumb--more">+{{ $lines->count() - 3 }}</span>
								@elseif(!empty($thumb))
									<span class="pos-retrieve-thumb" style="background-image:url({{ $thumb }})"></span>
								@else
									<span class="pos-retrieve-thumb pos-retrieve-thumb--blank"></span>
								@endif
							@empty
								<span class="pos-retrieve-thumb pos-retrieve-thumb--blank"></span>
							@endforelse
						</div>
						<div class="pos-retrieve-sale-meta">
							@foreach($preview_lines as $line)
								<div class="pos-retrieve-line">
									{{ @num_format($line->quantity) }} x {{ $line->product->name ?? 'Item' }}
								</div>
							@endforeach
							@if($extra_count > 0)
								<div class="pos-retrieve-line pos-retrieve-line--more">+{{ $extra_count }} more</div>
							@endif
							<div class="pos-retrieve-parked">
								Parked {{ $parked_ago }}@if($parked_by) by {{ \Illuminate\Support\Str::limit($parked_by, 12, '...') }}@endif
							</div>
							@if(!empty($sale->additional_notes))
								<div class="pos-retrieve-note">Note: {{ $sale->additional_notes }}</div>
							@endif
						</div>
					</div>
					<div class="pos-retrieve-customer">
						<span class="pos-retrieve-avatar" style="background:{{ $color }}">{{ $initials }}</span>
						<div class="pos-retrieve-customer-meta">
							<div class="pos-retrieve-customer-name">{{ $cust_name }}</div>
							@if(!empty($mobile))
								<div class="pos-retrieve-customer-sub">{{ $mobile }}</div>
							@elseif(!empty($email))
								<div class="pos-retrieve-customer-sub">{{ $email }}</div>
							@endif
						</div>
						<a href="{{ $edit_url }}" class="pos-retrieve-open" title="Open in cart">
							<i class="fas fa-external-link-alt"></i>
						</a>
						@if(auth()->user()->can('sell.delete') || auth()->user()->can('direct_sell.delete'))
							<a href="{{ action([\App\Http\Controllers\SellPosController::class, 'destroy'], ['po' => $sale->id]) }}"
								class="pos-retrieve-delete delete-sale is_suspended" title="@lang('messages.delete')">
								<i class="fas fa-trash"></i>
							</a>
						@endif
					</div>
				</div>
			@endif
		@empty
			<p class="pos-retrieve-empty">@lang('purchase.no_records_found')</p>
		@endforelse
	</div>
</div>
