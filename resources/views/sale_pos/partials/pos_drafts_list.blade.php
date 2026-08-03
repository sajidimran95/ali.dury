@php
    $subtype = '';
@endphp
@if (!empty($transaction_sub_type))
    @php
        $subtype = '?sub_type=' . $transaction_sub_type;
    @endphp
@endif

@if (!empty($transactions) && count($transactions) > 0)
    @foreach ($transactions as $transaction)
        <div class="pos-draft-card" data-transaction-id="{{ $transaction->id }}">
            <div class="pos-draft-card__main">
                <div class="pos-draft-card__top">
                    <span class="pos-draft-card__invoice">{{ $transaction->invoice_no }}</span>
                    <span class="pos-draft-card__total display_currency">{{ $transaction->final_total }}</span>
                </div>
                <div class="pos-draft-card__meta">
                    @php
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
                    <span class="pos-draft-card__customer" title="{{ $customerLabel }}">
                        <i class="fa fa-user"></i> {{ $customerLabel }}
                    </span>
                    <span class="pos-draft-card__date">{{ @format_datetime($transaction->transaction_date) }}</span>
                </div>
                @if (!empty($transaction->sale_note))
                    <div class="pos-draft-card__note" title="{{ $transaction->sale_note }}">
                        <i class="fa fa-sticky-note"></i> {{ Str::limit($transaction->sale_note, 48) }}
                    </div>
                @endif
                @if (!empty($transaction->table))
                    <div class="pos-draft-card__note">
                        <i class="fa fa-chair"></i> {{ $transaction->table->name }}
                    </div>
                @endif
            </div>
            <div class="pos-draft-card__actions">
                @if (auth()->user()->can('sell.update') || auth()->user()->can('direct_sell.update'))
                    <a href="{{ route('pos.edit', ['po' => $transaction->id]) . $subtype }}"
                        class="pos-draft-card__btn pos-draft-card__btn--resume"
                        title="@lang('lang_v1.resume_draft')">
                        <i class="fas fa-play"></i>
                        <span>@lang('lang_v1.resume')</span>
                    </a>
                @endif
                @if (auth()->user()->can('sell.delete') || auth()->user()->can('direct_sell.delete'))
                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'destroy'], [$transaction->id]) }}"
                        class="pos-draft-card__btn pos-draft-card__btn--delete delete-sale"
                        title="@lang('messages.delete')">
                        <i class="fa fa-trash"></i>
                    </a>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="pos-drafts-panel__empty">
        <i class="fas fa-inbox"></i>
        <p>
            @if (($transaction_status ?? 'draft') === 'suspended')
                @lang('lang_v1.pos_no_suspended')
            @else
                @lang('lang_v1.pos_no_drafts')
            @endif
        </p>
    </div>
@endif
