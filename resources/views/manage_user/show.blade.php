@extends('layouts.app')

@section('title', __( 'lang_v1.view_user' ))
@section('css')
<link rel="stylesheet" href="{{ asset('css/ls-users.css') }}?v=28">
@endsection

@section('content')
@php
    $img_src = isset($user->media->display_url) ? $user->media->display_url : 'https://ui-avatars.com/api/?name='.urlencode($user->first_name).'&background=7c3aed&color=fff';
    $initial = strtoupper(substr($user->first_name ?: 'U', 0, 1));
    $dailyT = $user->sales_target_daily;
    $weeklyT = $user->sales_target_weekly;
    $monthlyT = $user->sales_target_monthly ?? $user->sales_target;
    $outletLabel = '—';
    if (isset($permitted_locations) && $permitted_locations === 'all') {
        $outletLabel = 'All outlets';
    } elseif (is_array($permitted_locations ?? null) && !empty($locations)) {
        $names = [];
        foreach ($locations as $loc) {
            if (in_array($loc->id, $permitted_locations)) {
                $names[] = $loc->name;
            }
        }
        $outletLabel = $names ? implode(', ', $names) : '—';
    }
    $selected_contacts = __('lang_v1.all');
    if (count($user->contactAccess)) {
        $selected_contacts = $user->contactAccess->pluck('name')->implode(', ');
    }
    $chartDays = $salesStats['days'] ?? [];
    $custom_labels = json_decode(session('business.custom_labels'), true);
    $bank_details = !empty($user->bank_details) ? json_decode($user->bank_details, true) : [];
@endphp
<section class="content" style="padding:0;margin:0;width:100%;">
<div class="ls-users-page">
    <div class="ls-users-head">
        <div>
            <div class="ls-users-kicker">Settings</div>
            <h1 class="ls-users-title">Change {{ $user->first_name }}’s personal information including their role</h1>
        </div>
        <div class="ls-users-head-actions">
            <p class="ls-users-sub" style="margin:0;">Last updated {{ $user->updated_at ? $user->updated_at->diffForHumans() : '' }}</p>
            {!! Form::select('user_id', $users, $user->id , ['class' => 'form-control select2 ls-users-select', 'id' => 'user_id']); !!}
            @can('user.update')
                <a href="{{action([\App\Http\Controllers\ManageUserController::class, 'edit'], [$user->id])}}" class="ls-users-save" style="text-decoration:none;display:inline-flex;align-items:center;">
                    @lang("messages.edit")
                </a>
            @endcan
        </div>
    </div>
    <nav class="ls-users-tabs">
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'index']) }}">Users</a>
        <a href="{{ action([\App\Http\Controllers\RoleController::class, 'index']) }}">Roles</a>
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'show'], [$user->id]) }}" class="is-on">View</a>
    </nav>

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>Profile</h2>
            <p>Personal and contact information for this user.</p>
        </div>
        <div class="ls-sec__main">
            <div class="ls-sec__split">
                <div>
                    <div class="ls-name-row">
                        <div>
                            <label class="ls-ro-label">{{ __('business.prefix') }}</label>
                            <div class="ls-ro-input">{{ $user->surname ?: '—' }}</div>
                        </div>
                        <div>
                            <label class="ls-ro-label">{{ __('business.first_name') }}</label>
                            <div class="ls-ro-input">{{ $user->first_name }}</div>
                        </div>
                        <div>
                            <label class="ls-ro-label">{{ __('business.last_name') }}</label>
                            <div class="ls-ro-input">{{ $user->last_name ?: '—' }}</div>
                        </div>
                    </div>
                    <label class="ls-ro-label">Username</label>
                    <div class="ls-ro-input">{{ $user->username }}</div>
                    <label class="ls-ro-label">Display name</label>
                    <div class="ls-ro-input">{{ $user->user_full_name }}</div>
                    <label class="ls-ro-label">Email address</label>
                    <div class="ls-ro-input">{{ $user->email }}</div>
                    <label class="ls-ro-label">{{ __('lang_v1.status_for_user') }}</label>
                    <div class="ls-ro-input">{{ $user->status == 'active' ? __('business.is_active') : __('lang_v1.inactive') }}</div>
                    <p class="ls-ro-meta">Created at {{ $user->created_at ? $user->created_at->format('M j, Y \a\t g:i A') : '—' }}</p>
                </div>
                <div class="ls-profile-card">
                    <div class="ls-profile-card__top">
                        <img class="ls-avatar" src="{{ $img_src }}" alt="">
                        <div>
                            <strong>{{ $user->first_name }} ({{ $user->username }})</strong>
                            <div class="ls-ro-meta">{{ $user->email }}</div>
                        </div>
                    </div>
                    <p class="ls-ro-meta">The username and display name will be visible to your staff to help them identify this user. Sometimes the email address will also be visible.</p>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($isSalesRep))
    <div class="ls-two">
        <section class="ls-sec ls-sec--stack">
            <h2>Targets</h2>
            <div class="ls-target-row">
                <div><span>Today</span><strong>@format_currency($salesStats['today'] ?? 0)</strong></div>
                <em>@if($dailyT === null || $dailyT === '') No target set @else @format_currency($dailyT) @endif</em>
            </div>
            <div class="ls-target-row">
                <div><span>This week</span><strong>@format_currency($salesStats['week'] ?? 0)</strong></div>
                <em>@if($weeklyT === null || $weeklyT === '') No target set @else @format_currency($weeklyT) @endif</em>
            </div>
            <div class="ls-target-row">
                <div><span>This month</span><strong>@format_currency($salesStats['month'] ?? 0)</strong></div>
                <em>@if($monthlyT === null || $monthlyT === '') No target set @else @format_currency($monthlyT) @endif</em>
            </div>
        </section>
        <section class="ls-sec ls-sec--stack">
            <div class="ls-hist-head">
                <h2>History</h2>
                <div class="ls-hist-tabs">
                    <button type="button" class="is-on" data-hist="7">7 days</button>
                    <button type="button" data-hist="14">14 days</button>
                    <button type="button" data-hist="30">30 days</button>
                </div>
            </div>
            <div class="ls-hist-body">
                <div class="ls-hist-chart">
                    <svg id="lsHistChart" viewBox="0 0 320 140" preserveAspectRatio="none"></svg>
                </div>
                <div class="ls-hist-stats">
                    <div><span>Total sales</span><strong id="lsHistTotal">@format_currency($salesStats['total_7'] ?? 0)</strong></div>
                    <div><span>Average day total</span><strong id="lsHistAvg">@format_currency($salesStats['avg_day_7'] ?? 0)</strong></div>
                    <div><span>Average sale value</span><strong id="lsHistAvgSale">@format_currency($salesStats['avg_day_7'] ?? 0)</strong></div>
                </div>
            </div>
        </section>
    </div>
    @endif

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>Outlets</h2>
            <p>Assigned outlets for this user.</p>
        </div>
        <div class="ls-sec__main">
            <label class="ls-ro-label">Outlets</label>
            <div class="ls-ro-input">{{ $outletLabel }}</div>
        </div>
    </section>

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>Role</h2>
            <p>The role controls what this user can do.</p>
        </div>
        <div class="ls-sec__main">
            <div class="ls-ro-static">{{ $user->role_name }}</div>
        </div>
    </section>

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>Sales</h2>
            <p>Commission, discount and contact access.</p>
        </div>
        <div class="ls-sec__main">
            <label class="ls-ro-label">@lang( 'lang_v1.cmmsn_percent' )</label>
            <div class="ls-ro-input">{{ $user->cmmsn_percent }}%</div>
            <label class="ls-ro-label">@lang( 'lang_v1.max_sales_discount_percent' )</label>
            <div class="ls-ro-input">{{ $user->max_sales_discount_percent }}</div>
            <label class="ls-ro-label">@lang( 'lang_v1.allowed_contacts' )</label>
            <div class="ls-ro-input">{{ $selected_contacts }}</div>
        </div>
    </section>

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>Security and ID</h2>
            <p>Login, barcode switch and staff PIN.</p>
        </div>
        <div class="ls-sec__main">
            <label class="ls-ro-label">@lang( 'lang_v1.allow_login' )</label>
            <div class="ls-ro-input">{{ !empty($user->allow_login) ? 'Yes' : 'No' }}</div>
            <label class="ls-ro-label">Enable barcode</label>
            <div class="ls-ro-input">{{ !empty($user->barcode_login) ? 'On' : 'Off' }}</div>
            @php
                $barcodeCode = trim((string) ($user->barcode_switch_id ?? ''));
                $showBarcode = !empty($user->barcode_login) && $barcodeCode !== '';
            @endphp
            @if($showBarcode)
                @include('manage_user.partials.barcode_card', [
                    'barcodeCode' => $barcodeCode,
                    'showBarcode' => true,
                    'barcodeEditable' => true,
                    'barcodeAlways' => false,
                ])
            @elseif(!empty($user->barcode_login))
                <p class="ls-ro-meta">Barcode is on, but no switch ID is saved yet. Open Edit to generate one.</p>
            @endif
            <label class="ls-ro-label">{{ __('lang_v1.enable_service_staff_pin') }}</label>
            <div class="ls-ro-input">{{ !empty($user->is_enable_service_staff_pin) ? 'On' : 'Off' }}</div>
        </div>
    </section>

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>@lang('lang_v1.more_info')</h2>
            <p>All additional profile fields for this user.</p>
        </div>
        <div class="ls-sec__main">
            <div class="ls-fields">
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.dob')</label>
                    <div class="ls-ro-input">@if(!empty($user->dob)) {{@format_date($user->dob)}} @else — @endif</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.gender')</label>
                    <div class="ls-ro-input">@if(!empty($user->gender)) @lang('lang_v1.' .$user->gender) @else — @endif</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.marital_status')</label>
                    <div class="ls-ro-input">@if(!empty($user->marital_status)) @lang('lang_v1.' .$user->marital_status) @else — @endif</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.blood_group')</label>
                    <div class="ls-ro-input">{{ $user->blood_group ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.mobile_number')</label>
                    <div class="ls-ro-input">{{ $user->contact_number ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('business.alternate_number')</label>
                    <div class="ls-ro-input">{{ $user->alt_number ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.family_contact_number')</label>
                    <div class="ls-ro-input">{{ $user->family_number ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.fb_link')</label>
                    <div class="ls-ro-input">{{ $user->fb_link ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.twitter_link')</label>
                    <div class="ls-ro-input">{{ $user->twitter_link ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.social_media', ['number' => 1])</label>
                    <div class="ls-ro-input">{{ $user->social_media_1 ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.social_media', ['number' => 2])</label>
                    <div class="ls-ro-input">{{ $user->social_media_2 ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">{{ $custom_labels['user']['custom_field_1'] ?? __('lang_v1.user_custom_field1') }}</label>
                    <div class="ls-ro-input">{{ $user->custom_field_1 ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">{{ $custom_labels['user']['custom_field_2'] ?? __('lang_v1.user_custom_field2') }}</label>
                    <div class="ls-ro-input">{{ $user->custom_field_2 ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">{{ $custom_labels['user']['custom_field_3'] ?? __('lang_v1.user_custom_field3') }}</label>
                    <div class="ls-ro-input">{{ $user->custom_field_3 ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">{{ $custom_labels['user']['custom_field_4'] ?? __('lang_v1.user_custom_field4') }}</label>
                    <div class="ls-ro-input">{{ $user->custom_field_4 ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.id_proof_name')</label>
                    <div class="ls-ro-input">{{ $user->id_proof_name ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.id_proof_number')</label>
                    <div class="ls-ro-input">{{ $user->id_proof_number ?: '—' }}</div>
                </div>
            </div>
            <div class="ls-fields">
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.permanent_address')</label>
                    <div class="ls-ro-input">{{ $user->permanent_address ?: '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.current_address')</label>
                    <div class="ls-ro-input">{{ $user->current_address ?: '—' }}</div>
                </div>
            </div>
            <div class="ls-subh">@lang('lang_v1.bank_details')</div>
            <div class="ls-fields ls-fields--3">
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.account_holder_name')</label>
                    <div class="ls-ro-input">{{ $bank_details['account_holder_name'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.account_number')</label>
                    <div class="ls-ro-input">{{ $bank_details['account_number'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.bank_name')</label>
                    <div class="ls-ro-input">{{ $bank_details['bank_name'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.bank_code')</label>
                    <div class="ls-ro-input">{{ $bank_details['bank_code'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.branch')</label>
                    <div class="ls-ro-input">{{ $bank_details['branch'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="ls-ro-label">@lang('lang_v1.tax_payer_id')</label>
                    <div class="ls-ro-input">{{ $bank_details['tax_payer_id'] ?? '—' }}</div>
                </div>
            </div>
            @if(!empty($view_partials))
                @foreach($view_partials as $partial)
                    {!! $partial !!}
                @endforeach
            @endif
        </div>
    </section>

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>@lang('lang_v1.documents_and_notes')</h2>
            <p>Files and notes attached to this user.</p>
        </div>
        <div class="ls-sec__main">
            <input type="hidden" name="notable_id" id="notable_id" value="{{$user->id}}">
            <input type="hidden" name="notable_type" id="notable_type" value="App\User">
            <div class="document_note_body"></div>
        </div>
    </section>

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>@lang('lang_v1.activities')</h2>
            <p>Recent activity for this user.</p>
        </div>
        <div class="ls-sec__main">
            @include('activity_log.activities')
        </div>
    </section>

    @can('user.delete')
        @if(empty($isLastAdmin))
        <div class="ls-user-del">
            <button type="button" class="ls-user-del__btn delete_user_button" data-href="{{ action([\App\Http\Controllers\ManageUserController::class, 'destroy'], [$user->id]) }}">
                <i class="fas fa-trash-alt"></i> Delete user
            </button>
        </div>
        @endif
    @endcan
</div>
</section>
@endsection
@section('javascript')
    @include('documents_and_notes.document_and_note_js')
    <script type="text/javascript">
        $(document).ready( function(){
            $('#user_id').change( function() {
                if ($(this).val()) {
                    window.location = "{{url('/users')}}/" + $(this).val();
                }
            });
            $('.delete_user_button').on('click', function(){
                var href = $(this).data('href');
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_user,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(function (ok) {
                    if (!ok) return;
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                window.location = "{{ url('/users') }}";
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                });
            });
            var days = @json($chartDays);
            function money(n) {
                return (typeof __currency_trans_from_en === 'function') ? __currency_trans_from_en(n, true) : Number(n).toFixed(2);
            }
            function draw(count) {
                var slice = days.slice(-count);
                var max = 1;
                slice.forEach(function (d) { if (d.total > max) max = d.total; });
                var w = 320, h = 140, pad = 12;
                var pts = slice.map(function (d, i) {
                    var x = pad + (i * (w - pad * 2) / Math.max(slice.length - 1, 1));
                    var y = h - pad - ((d.total / max) * (h - pad * 2));
                    return x + ',' + y;
                }).join(' ');
                var dots = slice.map(function (d, i) {
                    var x = pad + (i * (w - pad * 2) / Math.max(slice.length - 1, 1));
                    var y = h - pad - ((d.total / max) * (h - pad * 2));
                    return '<circle cx="'+x+'" cy="'+y+'" r="3.5" fill="#7c3aed" />';
                }).join('');
                var svg = document.getElementById('lsHistChart');
                if (!svg) return;
                svg.innerHTML = '<polyline fill="none" stroke="#7c3aed" stroke-width="2" points="'+pts+'" />' + dots;
                var sum = slice.reduce(function (a, d) { return a + Number(d.total); }, 0);
                document.getElementById('lsHistTotal').textContent = money(sum);
                document.getElementById('lsHistAvg').textContent = money(slice.length ? sum / slice.length : 0);
                document.getElementById('lsHistAvgSale').textContent = money(slice.length ? sum / slice.length : 0);
            }
            draw(7);
            $('.ls-hist-tabs button').on('click', function () {
                $('.ls-hist-tabs button').removeClass('is-on');
                $(this).addClass('is-on');
                draw(parseInt($(this).data('hist'), 10));
            });
        });
    </script>
    @include('manage_user.partials.barcode_js')
@endsection
