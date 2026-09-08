@extends('layouts.app')

@section('title', __( 'user.edit_user' ))
@section('css')
<link rel="stylesheet" href="{{ asset('css/ls-users.css') }}">
@endsection

@section('content')
<section class="content" style="padding:0;margin:0;width:100%;">
<div class="ls-users-page">
    {!! Form::open(['url' => action([\App\Http\Controllers\ManageUserController::class, 'update'], [$user->id]), 'method' => 'PUT', 'id' => 'user_edit_form']) !!}
    <div class="ls-users-head">
        <div>
            <div class="ls-users-kicker">Settings</div>
            <h1 class="ls-users-title">Change {{ $user->first_name }}’s personal information including their role</h1>
            <p class="ls-users-sub">Last updated {{ $user->updated_at ? $user->updated_at->diffForHumans() : '' }}</p>
        </div>
        <button type="submit" class="ls-users-save" id="submit_user_button">Save</button>
    </div>
    <nav class="ls-users-tabs">
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'index']) }}">Users</a>
        <a href="{{ action([\App\Http\Controllers\RoleController::class, 'index']) }}">Roles</a>
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'edit'], [$user->id]) }}" class="is-on">Edit user</a>
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'show'], [$user->id]) }}">View</a>
    </nav>
    @include('manage_user.partials.form_sections', ['user' => $user])
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
<script type="text/javascript">
  $(document).ready(function(){
    __page_leave_confirmation('#user_edit_form');
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
    $('#lsCopyId').on('click', function () {
        var t = $(this).data('id');
        if (navigator.clipboard) navigator.clipboard.writeText(t);
        toastr.success('Copied');
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
