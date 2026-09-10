@php
    $isEdit = !empty($user);
    $u = $isEdit ? $user : null;
    $previewName = $isEdit ? $u->first_name : '';
    $previewUser = $isEdit ? $u->username : '';
    $previewEmail = $isEdit ? $u->email : '';
    $img_src = ($isEdit && isset($u->media->display_url))
        ? $u->media->display_url
        : 'https://ui-avatars.com/api/?name='.urlencode($previewName ?: 'U').'&background=7c3aed&color=fff';
    $permitted_locations = $permitted_locations ?? [];
    $contact_access = $contact_access ?? [];
    $is_checked_checkbox = $is_checked_checkbox ?? true;
    $username_ext = $username_ext ?? null;
    $isAdminUser = $isEdit && !empty($u) && method_exists($u, 'getRoleNames')
        ? collect($u->getRoleNames())->contains(function ($name) {
            return strtolower(trim(explode('#', (string) $name)[0])) === 'admin';
        })
        : false;
@endphp

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
                        {!! Form::text('surname', $isEdit ? $u->surname : null, ['class' => 'form-control', 'placeholder' => __('business.prefix_placeholder')]); !!}
                    </div>
                    <div>
                        <label class="ls-ro-label">{{ __('business.first_name') }} *</label>
                        {!! Form::text('first_name', $isEdit ? $u->first_name : null, ['class' => 'form-control', 'required', 'placeholder' => __('business.first_name'), 'id' => 'first_name']); !!}
                    </div>
                    <div>
                        <label class="ls-ro-label">{{ __('business.last_name') }}</label>
                        {!! Form::text('last_name', $isEdit ? $u->last_name : null, ['class' => 'form-control', 'placeholder' => __('business.last_name'), 'id' => 'last_name']); !!}
                    </div>
                </div>
                <label class="ls-ro-label">Username</label>
                @if($isEdit && !empty($u->username) && !empty($u->allow_login))
                    {!! Form::text('username_display', $u->username, ['class' => 'form-control', 'readonly' => true, 'id' => 'username_display']); !!}
                    {!! Form::hidden('username_locked', $u->username, ['id' => 'username_locked']) !!}
                @elseif(!empty($username_ext))
                    <div class="input-group">
                        {!! Form::text('username', $isEdit ? $u->username : null, ['class' => 'form-control', 'placeholder' => __('business.username'), 'id' => 'username']); !!}
                        <span class="input-group-addon">{{ $username_ext }}</span>
                    </div>
                    <p class="help-block" id="show_username"></p>
                @else
                    {!! Form::text('username', $isEdit ? ($u->username ?? null) : null, ['class' => 'form-control', 'placeholder' => __('business.username'), 'id' => 'username']); !!}
                @endif
                <label class="ls-ro-label">Display name</label>
                {!! Form::text('display_name', $isEdit ? $u->user_full_name : null, ['class' => 'form-control', 'id' => 'ls_display_name', 'readonly' => true, 'placeholder' => 'Display name']); !!}
                <label class="ls-ro-label">Email address *</label>
                {!! Form::text('email', $isEdit ? $u->email : null, ['class' => 'form-control', 'required', 'placeholder' => __('business.email'), 'id' => 'email']); !!}
                <label class="ls-ro-label">{{ __('lang_v1.status_for_user') }}</label>
                @if(!empty($isAdminUser))
                    {!! Form::hidden('is_active', 'active') !!}
                    {!! Form::text('status_display', __('business.is_active'), ['class' => 'form-control', 'readonly' => true]); !!}
                @else
                    <div class="ls-check-row" style="margin-top:0;">
                        <label>
                            {!! Form::checkbox('is_active', $isEdit ? $u->status : 'active', $isEdit ? $is_checked_checkbox : true, ['class' => 'input-icheck status']); !!}
                            {{ __('lang_v1.status_for_user') }}
                        </label>
                        @show_tooltip(__('lang_v1.tooltip_enable_user_active'))
                    </div>
                @endif
                @if($isEdit)
                    <p class="ls-ro-meta">Created at {{ $u->created_at ? $u->created_at->format('M j, Y \a\t g:i A') : '—' }}</p>
                @endif
            </div>
            <div class="ls-profile-card">
                <div class="ls-profile-card__top">
                    <img class="ls-avatar" src="{{ $img_src }}" alt="">
                    <div>
                        <strong id="lsPreviewName">{{ $previewName ?: 'Display name' }} @if($previewUser)({{ $previewUser }})@endif</strong>
                        <div class="ls-ro-meta" id="lsPreviewEmail">{{ $previewEmail ?: 'Email address' }}</div>
                    </div>
                </div>
                <p class="ls-ro-meta">The username and display name will be visible to your staff to help them identify this user. Sometimes the email address will also be visible.</p>
            </div>
        </div>
    </div>
    </section>

    @if($isEdit && !empty($isSalesRep) && isset($salesStats))
        @include('manage_user.partials.targets_history')
    @endif

    <section class="ls-sec">
        <div class="ls-sec__side">
            <h2>Outlets</h2>
        <p>Assigned outlets for this user that they can work at.</p>
    </div>
    <div class="ls-sec__main">
        <label class="ls-ro-label">@lang('role.access_locations')</label>
        <div class="ls-check-row">
            <label>
                {!! Form::checkbox('access_all_locations', 'access_all_locations', !$isEdit || (!is_array($permitted_locations) && $permitted_locations == 'all'), ['class' => 'input-icheck']); !!}
                {{ __('role.all_locations') }}
            </label>
            @show_tooltip(__('tooltip.all_location_permission'))
        </div>
        @foreach($locations as $location)
            <div class="ls-check-row">
                <label>
                    {!! Form::checkbox('location_permissions[]', 'location.'.$location->id, $isEdit && is_array($permitted_locations) && in_array($location->id, $permitted_locations), ['class' => 'input-icheck']); !!}
                    {{ $location->name }} @if(!empty($location->location_id))({{ $location->location_id }})@endif
                </label>
            </div>
        @endforeach
    </div>
</section>

<section class="ls-sec">
    <div class="ls-sec__side">
        <h2>Role</h2>
        <p>A role defines what this user can see and do.</p>
    </div>
    <div class="ls-sec__main">
        <label class="ls-ro-label">{{ __('user.role') }} *</label>
        {!! Form::select('role', $roles, $isEdit && !empty($u->roles->first()->id) ? $u->roles->first()->id : null, ['class' => 'form-control select2', 'style' => 'width:100%;']); !!}
        @show_tooltip(__('lang_v1.admin_role_location_permission_help'))
    </div>
</section>

<section class="ls-sec">
    <div class="ls-sec__side">
        <h2>Sales</h2>
        <p>Commission, discount and contact access.</p>
    </div>
    <div class="ls-sec__main">
        <div class="ls-fields">
            <div>
                <label class="ls-ro-label">@lang('lang_v1.cmmsn_percent')</label>
                {!! Form::text('cmmsn_percent', $isEdit && !empty($u->cmmsn_percent) ? @num_format($u->cmmsn_percent) : null, ['class' => 'form-control input_number', 'placeholder' => __('lang_v1.cmmsn_percent')]); !!}
            </div>
            <div>
                <label class="ls-ro-label">@lang('lang_v1.max_sales_discount_percent')</label>
                {!! Form::text('max_sales_discount_percent', $isEdit && !is_null($u->max_sales_discount_percent) ? @num_format($u->max_sales_discount_percent) : null, ['class' => 'form-control input_number', 'placeholder' => __('lang_v1.max_sales_discount_percent')]); !!}
            </div>
        </div>
        <div class="ls-check-row">
            <label>
                {!! Form::checkbox('selected_contacts', 1, $isEdit ? $u->selected_contacts : false, ['class' => 'input-icheck', 'id' => 'selected_contacts']); !!}
                {{ __('lang_v1.allow_selected_contacts') }}
            </label>
            @show_tooltip(__('lang_v1.allow_selected_contacts_tooltip'))
        </div>
        <div class="selected_contacts_div @if($isEdit && !$u->selected_contacts) hide @elseif(!$isEdit) hide @endif">
            <label class="ls-ro-label">@lang('lang_v1.selected_contacts')</label>
            {!! Form::select('selected_contact_ids[]', $contact_access, $isEdit ? array_keys($contact_access) : null, ['class' => 'form-control select2', 'multiple', 'style' => 'width:100%;', 'id' => 'user_allowed_contacts']); !!}
        </div>
    </div>
</section>

<section class="ls-sec">
    <div class="ls-sec__side">
        <h2>Security and ID</h2>
        <p>Use a secure password. You can also set a staff PIN.</p>
    </div>
    <div class="ls-sec__main">
        <div class="ls-check-row">
            <label>
                {!! Form::checkbox('allow_login', 1, $isEdit ? !empty($u->allow_login) : true, ['class' => 'input-icheck', 'id' => 'allow_login']); !!}
                {{ __('lang_v1.allow_login') }}
            </label>
        </div>
        <div class="user_auth_fields @if($isEdit && empty($u->allow_login)) hide @endif">
            <div class="ls-subh">CHANGE PASSWORD</div>
            <div class="ls-pass-row">
                <div>
                    <label class="ls-ro-label">{{ __('business.password') }} @if(!$isEdit)*@endif</label>
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => __('business.password'), 'id' => 'password', 'required' => !$isEdit || empty($u->allow_login)]); !!}
                    @if($isEdit)
                        <p class="help-block">@lang('user.leave_password_blank')</p>
                    @endif
                </div>
                <div>
                    <label class="ls-ro-label">{{ __('business.confirm_password') }} @if(!$isEdit)*@endif</label>
                    {!! Form::password('confirm_password', ['class' => 'form-control', 'placeholder' => __('business.confirm_password'), 'id' => 'confirm_password', 'required' => !$isEdit || empty($u->allow_login)]); !!}
                </div>
            </div>
        </div>
        <div class="ls-check-row">
            <label>
                {!! Form::checkbox('barcode_login', 1, $isEdit ? !empty($u->barcode_login) : false, ['class' => 'input-icheck', 'id' => 'barcode_login']); !!}
                Enable barcode
            </label>
        </div>
        @php
            $barcodeCode = '';
            if ($isEdit && !empty($u)) {
                $barcodeCode = $u->barcode_switch_id ?: (method_exists($u, 'ensureBarcodeSwitchId') ? $u->ensureBarcodeSwitchId() : '');
            }
            // Edit page: always show the barcode card so staff can print/copy the ID.
            $showBarcode = $isEdit && $barcodeCode !== '';
        @endphp
        {!! Form::hidden('barcode_switch_id', $barcodeCode, ['id' => 'barcode_switch_id']) !!}
        @include('manage_user.partials.barcode_card', [
            'barcodeCode' => $barcodeCode,
            'showBarcode' => $showBarcode,
            'barcodeEditable' => true,
            'barcodeAlways' => $isEdit,
        ])
        <div class="ls-check-row">
            <label>
                {!! Form::checkbox('is_enable_service_staff_pin', 1, $isEdit ? $u->is_enable_service_staff_pin : false, ['class' => 'input-icheck status', 'id' => 'is_enable_service_staff_pin']); !!}
                {{ __('lang_v1.enable_service_staff_pin') }}
            </label>
            @show_tooltip(__('lang_v1.tooltip_is_enable_service_staff_pin'))
        </div>
        <div class="service_staff_pin_div @if($isEdit && $u->is_enable_service_staff_pin == 1) @elseif($isEdit) hide @else hide @endif">
            <label class="ls-ro-label">{{ __('lang_v1.staff_pin') }}</label>
            {!! Form::password('service_staff_pin', ['class' => 'form-control', 'placeholder' => __('lang_v1.staff_pin'), 'id' => 'service_staff_pin']); !!}
        </div>
    </div>
</section>

<section class="ls-sec">
    <div class="ls-sec__side">
        <h2>@lang('lang_v1.more_info')</h2>
        <p>All additional profile fields for this user.</p>
    </div>
    <div class="ls-sec__main">
        <div class="row" style="margin:0;">
            @include('user.form', ['user' => $u ?: (object) [], 'bank_details' => $isEdit && !empty($u->bank_details) ? json_decode($u->bank_details, true) : null])
        </div>
        @if(!empty($form_partials))
            @foreach($form_partials as $partial)
                {!! $partial !!}
            @endforeach
        @endif
    </div>
</section>
