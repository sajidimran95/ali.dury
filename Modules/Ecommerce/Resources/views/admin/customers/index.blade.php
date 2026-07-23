@extends('layouts.app')
@section('title', __('ecommerce::lang.customers'))
@section('content')
<section class="content-header">
    <h1>@lang('ecommerce::lang.customers')</h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-header with-border">
            <form method="get" class="form-inline">
                <label class="m-r-5">Status</label>
                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status')==='pending')>Pending</option>
                    <option value="approved" @selected(request('status')==='approved')>Approved</option>
                    <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
                </select>
            </form>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Approval</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($customers as $c)
                    @php $st = \Modules\Ecommerce\Support\WholesaleAccount::status($c); @endphp
                    <tr>
                        <td>{{ $c->supplier_business_name }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->mobile }}</td>
                        <td>
                            @if($st === 'pending')
                                <span class="label label-warning">Pending</span>
                            @elseif($st === 'rejected')
                                <span class="label label-danger">Rejected</span>
                            @else
                                <span class="label label-success">Approved</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('ecommerce.admin.customers.show', $c->id) }}" class="btn btn-xs btn-primary">Review</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $customers->appends(request()->query())->links() }}
        </div>
    </div>
</section>
@endsection
