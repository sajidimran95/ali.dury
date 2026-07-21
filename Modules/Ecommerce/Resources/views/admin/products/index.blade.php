@extends('layouts.app')
@section('title', __('ecommerce::lang.products'))
@section('content')
<section class="content-header">
    <h1>@lang('ecommerce::lang.products')
        <small><a href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}">Add product</a></small>
    </h1>
</section>
<section class="content">
<div class="box box-solid">
    <div class="box-body table-responsive">
        <table class="table table-bordered">
            <thead><tr><th>Name</th><th>SKU</th><th>Channel</th><th>Online Price</th><th>Featured</th></tr></thead>
            <tbody>
            @foreach($products as $product)
                <tr>
                    <td><a href="{{ action([\App\Http\Controllers\ProductController::class, 'edit'], [$product->id]) }}">{{ $product->name }}</a></td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->sales_channel }}</td>
                    <td>{{ $product->ecom_discount_price ?: $product->ecom_online_price }}</td>
                    <td>{{ $product->ecom_is_featured ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $products->links() }}
    </div>
</div>
</section>
@endsection
