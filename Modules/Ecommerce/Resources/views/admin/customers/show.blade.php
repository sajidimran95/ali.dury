@extends('layouts.app')
@section('title', 'Wholesale customer')
@section('content')
<section class="content-header">
    <h1>
        {{ $customer->supplier_business_name ?: $customer->name }}
        <small>{{ \Modules\Ecommerce\Support\WholesaleAccount::label($approval) }}</small>
    </h1>
</section>
<section class="content">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Account</h3></div>
                <div class="box-body">
                    <p><strong>Contact:</strong> {{ $customer->name }}</p>
                    <p><strong>Email:</strong> {{ $customer->email }}</p>
                    <p><strong>Phone:</strong> {{ $customer->mobile }}</p>
                    <p><strong>Tax ID:</strong> {{ $customer->tax_number }}</p>
                    <p><strong>Address:</strong><br>
                        {{ $customer->address_line_1 }} {{ $customer->address_line_2 }}<br>
                        {{ $customer->city }}, {{ $customer->state }} {{ $customer->zip_code }}
                    </p>
                    <p><strong>Status:</strong>
                        @if($approval === 'pending')
                            <span class="label label-warning">Pending</span>
                        @elseif($approval === 'rejected')
                            <span class="label label-danger">Rejected</span>
                        @else
                            <span class="label label-success">Approved</span>
                        @endif
                    </p>
                </div>
                <div class="box-footer">
                    <form method="post" action="{{ route('ecommerce.admin.customers.status', $customer->id) }}" class="form-inline">
                        @csrf
                        <button name="status" value="approved" class="btn btn-success btn-sm">Approve</button>
                        <button name="status" value="pending" class="btn btn-warning btn-sm">Set pending</button>
                        <button name="status" value="rejected" class="btn btn-danger btn-sm">Reject</button>
                        <a href="{{ route('ecommerce.admin.customers') }}" class="btn btn-default btn-sm">Back</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="box box-solid">
                <div class="box-header with-border"><h3 class="box-title">Licenses / certificates</h3></div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Number</th>
                                <th>Expiry</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($licenses as $lic)
                            <tr>
                                <td>{{ $lic->label ?: $lic->license_type }}</td>
                                <td>{{ $lic->certificate_number ?: '—' }}</td>
                                <td>{{ $lic->expires_at ? $lic->expires_at->format('Y-m-d') : '—' }}</td>
                                <td>
                                    @if($lic->file_path)
                                        <a href="{{ asset('uploads/'.$lic->file_path) }}" target="_blank">{{ $lic->original_name ?: 'View file' }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No licenses uploaded.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
