@extends('layouts.app')

@section('title', __( 'user.add_user' ))
@section('css')
<link rel="stylesheet" href="{{ asset('css/ls-users.css') }}?v=28">
@endsection

@section('content')
<section class="content-header ls-users-page-head">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('user.add_user')</h1>
    <button type="submit" form="user_add_form" class="ls-users-save" id="submit_user_button">@lang('messages.save')</button>
</section>
<section class="content" style="padding:0;margin:0;width:100%;">
<div class="ls-users-page">
{!! Form::open(['url' => action([\App\Http\Controllers\ManageUserController::class, 'store']), 'method' => 'post', 'id' => 'user_add_form' ]) !!}
    <nav class="ls-users-tabs">
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'index']) }}">Users</a>
        <a href="{{ action([\App\Http\Controllers\RoleController::class, 'index']) }}">Roles</a>
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'create']) }}" class="is-on">Add user</a>
    </nav>
    @include('manage_user.partials.form_sections')
    <div style="text-align:right;padding:16px 0 24px;">
      <button type="submit" class="ls-users-save">@lang( 'messages.save' )</button>
    </div>
{!! Form::close() !!}
</div>
</section>
@stop
@section('javascript')
<script type="text/javascript">
  __page_leave_confirmation('#user_add_form');
  $(document).ready(function(){
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
  $('form#user_add_form').validate({
                rules: {
                    first_name: { required: true },
                    email: {
                        email: true,
                        remote: {
                            url: "/business/register/check-email",
                            type: "post",
                            data: { email: function() { return $( "#email" ).val(); } }
                        }
                    },
                    password: { required: true, minlength: 5 },
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
  $('#username').change( function(){
    if($('#show_username').length > 0){
      if($(this).val().trim() != ''){
        $('#show_username').html("{{__('lang_v1.your_username_will_be')}}: <b>" + $(this).val() + "{{$username_ext}}</b>");
      } else {
        $('#show_username').html('');
      }
    }
  });
</script>
@include('manage_user.partials.barcode_js')
@endsection
