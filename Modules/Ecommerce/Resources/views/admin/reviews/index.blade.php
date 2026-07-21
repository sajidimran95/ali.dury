@extends('layouts.app')
@section('title', __('ecommerce::lang.reviews'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.reviews')</h1></section>
<section class="content">
<div class="box"><div class="box-body table-responsive">
<table class="table table-bordered">
<thead><tr><th>Product</th><th>Author</th><th>Rating</th><th>Review</th><th>Status</th><th></th></tr></thead>
<tbody>
@foreach($reviews as $review)
<tr>
<td>{{ optional($review->product)->name }}</td>
<td>{{ $review->author_name }}</td>
<td>{{ $review->rating }}</td>
<td>{{ $review->review }}</td>
<td>{{ $review->status }}</td>
<td>
<form method="post" action="{{ route('ecommerce.admin.reviews.status',$review->id) }}" class="form-inline">@csrf
<select name="status" class="form-control input-sm"><option value="approved">Approve</option><option value="rejected">Reject</option><option value="pending">Pending</option></select>
<button class="btn btn-xs btn-primary">Save</button>
</form>
</td>
</tr>
@endforeach
</tbody></table>
{{ $reviews->links() }}
</div></div>
</section>
@endsection
