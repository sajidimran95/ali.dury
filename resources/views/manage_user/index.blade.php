@extends('layouts.app')
@section('title', __( 'user.users' ))
@section('css')
<link rel="stylesheet" href="{{ asset('css/ls-users.css') }}?v=25">
@endsection

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('user.users')
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('user.manage_users')</small>
    </h1>
</section>
<section class="content" style="padding:0;margin:0;width:100%;">
<div class="ls-users-page">
    <nav class="ls-users-tabs">
        <a href="{{ action([\App\Http\Controllers\ManageUserController::class, 'index']) }}" class="is-on">Users</a>
        <a href="{{ action([\App\Http\Controllers\RoleController::class, 'index']) }}">Roles</a>
    </nav>

    <div class="ls-users-tools">
        <input type="search" id="ls_user_search" class="ls-users-search" placeholder="Search users">
        <select id="ls_role_filter" class="ls-users-select">
            <option value="">All roles</option>
            @foreach($roles as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
        <select id="ls_outlet_filter" class="ls-users-select">
            <option value="">All outlets</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}">{{ $location->name }}</option>
            @endforeach
        </select>
        @can('user.create')
            <a class="ls-users-add" href="{{ action([\App\Http\Controllers\ManageUserController::class, 'create']) }}">
                <i class="fa fa-plus"></i> Add user
            </a>
        @endcan
    </div>

    @can('user.view')
    <div class="ls-users-card table-responsive">
        <table class="table ls-users-table" id="users_table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Outlet</th>
                    <th>Daily target</th>
                    <th>Weekly target</th>
                    <th>Monthly target</th>
                    <th>Last active</th>
                    <th>Enabled</th>
                    <th class="not-export">@lang( 'messages.action' )</th>
                </tr>
            </thead>
        </table>
    </div>
    @endcan

    <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</div>
</section>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready( function(){
        var users_table = $('#users_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    searching: true,
                    dom: 't<"ls-users-foot"ip>',
                    ajax: {
                        url: '/users',
                        data: function (d) {
                            d.role_id = $('#ls_role_filter').val();
                            d.location_id = $('#ls_outlet_filter').val();
                        }
                    },
                    columnDefs: [ {
                        "targets": [7, 8],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"user_cell"},
                        {"data":"role"},
                        {"data":"outlet", "orderable": false, "searchable": false},
                        {"data":"target_daily", "orderable": false, "searchable": false},
                        {"data":"target_weekly", "orderable": false, "searchable": false},
                        {"data":"target_monthly", "orderable": false, "searchable": false},
                        {"data":"last_active", "orderable": false, "searchable": false},
                        {"data":"enabled"},
                        {"data":"action"}
                    ]
                });
        $('#ls_user_search').on('keyup', function () {
            users_table.search(this.value).draw();
        });
        $('#ls_role_filter, #ls_outlet_filter').on('change', function () {
            users_table.ajax.reload();
        });
        $(document).on('change', '.ls-user-enabled', function(){
            var $sw = $(this);
            var id = $sw.data('id');
            var enabled = this.checked ? 1 : 0;
            $.ajax({
                method: 'POST',
                url: (typeof base_path !== 'undefined' ? base_path : '') + '/users/' + id + '/toggle-enabled',
                data: { _token: $('meta[name="csrf-token"]').attr('content'), enabled: enabled },
                success: function (result) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success((result && result.msg) ? result.msg : (enabled ? 'User enabled successfully.' : 'User disabled successfully.'));
                    }
                },
                error: function (xhr) {
                    $sw.prop('checked', !enabled);
                    if (typeof toastr !== 'undefined') {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Could not update user status');
                    }
                }
            });
        });
        $(document).on('keydown keyup click', '.ls-target-input', function (e) {
            e.stopPropagation();
        });
        $(document).on('change', '.ls-target-input', function () {
            var $el = $(this);
            var id = $el.attr('data-id');
            var field = $el.attr('data-field');
            if (!id || !field) return;
            $.ajax({
                method: 'POST',
                url: (typeof base_path !== 'undefined' ? base_path : '') + '/users/' + id + '/sales-targets',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    field: field,
                    value: $el.val()
                },
                success: function (result) {
                    if (result && result.success && typeof toastr !== 'undefined') {
                        toastr.success('Target saved');
                    }
                },
                error: function () {
                    if (typeof toastr !== 'undefined') toastr.error('Could not save target');
                }
            });
        });
        $(document).on('keydown', '.ls-target-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).blur();
            }
        });
        $(document).on('click', 'button.delete_user_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_user,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                users_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
             });
        });
    });
</script>
@endsection
