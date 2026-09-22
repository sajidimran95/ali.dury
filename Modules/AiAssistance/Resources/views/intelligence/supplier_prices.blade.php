@extends('layouts.app')
@section('title', 'Cheaper supplier prices')
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">Cheaper supplier prices
        <small>Which supplier gives the better unit cost (last 12 months)</small>
    </h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <p class="help-block">Average purchase / PO unit cost per SKU. Shown only when two or more suppliers sold you the same item. This is advice only — nothing is auto-ordered.</p>
            <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Cheapest supplier</th>
                        <th>Avg cost</th>
                        <th>Highest supplier</th>
                        <th>Avg cost</th>
                        <th>Save %</th>
                        <th>All suppliers (avg)</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['product'] }}</td>
                        <td>{{ $row['sku'] }}</td>
                        <td><strong>{{ $row['cheapest_supplier'] }}</strong></td>
                        <td>{{ $symbol }}{{ number_format($row['cheapest_avg'], 2) }}</td>
                        <td>{{ $row['costliest_supplier'] }}</td>
                        <td>{{ $symbol }}{{ number_format($row['costliest_avg'], 2) }}</td>
                        <td>{{ $row['save_pct'] }}%</td>
                        <td>
                            @foreach($row['suppliers'] as $sup)
                                {{ $sup['supplier'] }}: {{ $symbol }}{{ number_format($sup['avg_cost'], 2) }}@if(!$loop->last); @endif
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No SKUs with two or more suppliers and different prices in the last 12 months.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
