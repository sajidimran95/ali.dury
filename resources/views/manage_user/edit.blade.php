@extends('layouts.app')

@section('title', __( 'user.edit_user' ))
@section('css')
<link rel="stylesheet" href="{{ asset('css/ls-users.css') }}?v=24">
@endsection

@section('content')
<section class="content-header ls-users-page-head">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('user.edit_user')</h1>
    <div class="ls-users-head-actions">
        @if(!empty($users))
            {!! Form::select('user_switch', $users, $user->id , ['class' => 'form-control select2 ls-users-select', 'id' => 'user_id']); !!}
        @endif
        <button type="submit" form="user_edit_form" class="ls-users-save" id="submit_user_button">@lang('messages.save')</button>
    </div>
</section>
<section class="content" style="padding:0;margin:0;width:100%;">
<div class="ls-users-page">
    {!! Form::open(['url' => action([\App\Http\Controllers\ManageUserController::class, 'update'], [$user->id]), 'method' => 'PUT', 'id' => 'user_edit_form']) !!}
    <nav class="ls-users-tabs">
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'index']) }}">Users</a>
        <a href="{{ action([\App\Http\Controllers\RoleController::class, 'index']) }}">Roles</a>
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'edit'], [$user->id]) }}" class="is-on">Edit user</a>
    </nav>
    @include('manage_user.partials.form_sections', ['user' => $user, 'isSalesRep' => $isSalesRep ?? false, 'salesStats' => $salesStats ?? []])

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
    {!! Form::close() !!}
</div>
</section>
@stop
@section('javascript')
@include('documents_and_notes.document_and_note_js')
<script type="text/javascript">
$(document).ready(function(){
    if (typeof __page_leave_confirmation === 'function') {
      __page_leave_confirmation('#user_edit_form');
    }
    $('#user_id').change(function () {
      if ($(this).val()) {
        window.location = "{{ url('/users') }}/" + $(this).val() + "/edit";
      }
    });
    function syncDisplayName() {
      var n = [($('#first_name').val() || ''), ($('#last_name').val() || '')].join(' ').trim();
      $('#ls_display_name').val(n);
      var un = $('#username').val() || '{{ $user->username }}';
      $('#lsPreviewName').text(n ? (n + (un ? ' (' + un + ')' : '')) : 'Display name');
    }
    $('#first_name, #last_name').on('input', syncDisplayName);
    $('#email').on('input', function () {
      $('#lsPreviewEmail').text($(this).val() || 'Email address');
    });
    @if(!empty($isSalesRep))
    var days = @json($salesStats['days'] ?? []);
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
      if (document.getElementById('lsHistTotal')) {
        document.getElementById('lsHistTotal').textContent = money(sum);
        document.getElementById('lsHistAvg').textContent = money(slice.length ? sum / slice.length : 0);
        document.getElementById('lsHistAvgSale').textContent = money(slice.length ? sum / slice.length : 0);
      }
    }
    draw(7);
    $('.ls-hist-tabs button').on('click', function () {
      $('.ls-hist-tabs button').removeClass('is-on');
      $(this).addClass('is-on');
      draw(parseInt($(this).data('hist'), 10));
    });
    @endif
    $('#selected_contacts').on('ifChecked', function(event){
      $('div.selected_contacts_div').removeClass('hide');
    });
    $('#selected_contacts').on('ifUnchecked', function(event){
      $('div.selected_contacts_div').addClass('hide');
    });
    $('#is_enable_service_staff_pin').on('ifChecked', function(event){
      $('div.service_staff_pin_div').removeClass('hide');
    });
    $('#is_enable_service_staff_pin').on('ifUnchecked', function(event){
      $('div.service_staff_pin_div').addClass('hide');
      $('#service_staff_pin').val('');
    });
    $('#allow_login').on('ifChecked', function(event){
      $('div.user_auth_fields').removeClass('hide');
    });
    $('#allow_login').on('ifUnchecked', function(event){
      $('div.user_auth_fields').addClass('hide');
    });
    $('.delete_user_button').on('click', function(){
        var href = $(this).data('href');
        swal({ title: LANG.sure, text: LANG.confirm_delete_user, icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) {
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
    $('#user_allowed_contacts').select2({
        ajax: {
            url: '/contacts/customers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, page: params.page, all_contact: true };
            },
            processResults: function(data) { return { results: data }; },
        },
        templateResult: function (data) {
            var template = '';
            if (data.supplier_business_name) template += data.supplier_business_name + "<br>";
            template += data.text + "<br>" + LANG.mobile + ": " + data.mobile;
            return template;
        },
        minimumInputLength: 1,
        escapeMarkup: function(markup) { return markup; },
    });
  });
  $('form#user_edit_form').validate({
                rules: {
                    first_name: { required: true },
                    email: {
                        email: true,
                        remote: {
                            url: "/business/register/check-email",
                            type: "post",
                            data: { email: function() { return $( "#email" ).val(); }, user_id: {{$user->id}} }
                        }
                    },
                    password: { minlength: 5 },
                    confirm_password: { equalTo: "#password" },
                    username: {
                        minlength: 5,
                        remote: {
                            url: "/business/register/check-username",
                            type: "post",
                            data: {
                                username: function() { return $( "#username" ).val(); },
                                @if(!empty($username_ext))
                                  username_ext: "{{$username_ext}}"
                                @endif
                            }
                        }
                    }
                },
                messages: {
                    password: { minlength: 'Password should be minimum 5 characters' },
                    confirm_password: { equalTo: 'Should be same as password' },
                    username: { remote: 'Invalid username or User already exist' },
                    email: { remote: '{{ __("validation.unique", ["attribute" => __("business.email")]) }}' }
                }
            });
</script>
@endsection
