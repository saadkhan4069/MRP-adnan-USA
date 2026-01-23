@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
            <div>
                <a href="{{ route('woocommerce.orders.edit', $order->id) }}" class="btn btn-warning">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <a href="{{ route('woocommerce.orders.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Order Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Order Number:</th>
                            <td>{{ $order->order_number }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @php
                                    $badges = [
                                        'pending' => 'badge-warning',
                                        'processing' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-danger',
                                        'refunded' => 'badge-secondary',
                                    ];
                                    $badge = $badges[$order->status] ?? 'badge-secondary';
                                @endphp
                                <span class="badge {{ $badge }}">{{ ucfirst($order->status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td><strong>{{ $order->currency }} {{ number_format($order->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Subtotal:</th>
                            <td>{{ $order->currency }} {{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Shipping:</th>
                            <td>{{ $order->currency }} {{ number_format($order->shipping_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Tax:</th>
                            <td>{{ $order->currency }} {{ number_format($order->tax_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>{{ $order->currency }} {{ number_format($order->discount_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Order Date:</th>
                            <td>{{ $order->order_date ? $order->order_date->format('Y-m-d H:i:s') : '—' }}</td>
                        </tr>
                        @if($order->apiSetting)
                        <tr>
                            <th>Platform:</th>
                            <td>{{ $order->apiSetting->platform_name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Customer Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Customer:</th>
                            <td>
                                @if($order->customer)
                                    <a href="{{ route('customer.show', $order->customer->id) }}">{{ $order->customer->name }}</a>
                                @else
                                    {{ $order->customer_first_name }} {{ $order->customer_last_name }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $order->customer_email ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $order->customer_phone ?: '—' }}</td>
                        </tr>
                    </table>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Payment Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Payment Method:</th>
                            <td>{{ $order->payment_method_title ?: ($order->payment_method ?: '—') }}</td>
                        </tr>
                        @if($order->transaction_id)
                        <tr>
                            <th>Transaction ID:</th>
                            <td>{{ $order->transaction_id }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Billing Address</h6>
                    <address>
                        {{ $order->billing_address ?: '—' }}<br>
                        @if($order->billing_city || $order->billing_state || $order->billing_postcode)
                            {{ $order->billing_city }}{{ $order->billing_city && $order->billing_state ? ', ' : '' }}{{ $order->billing_state }}<br>
                            {{ $order->billing_postcode }}<br>
                        @endif
                        {{ $order->billing_country ?: '' }}
                    </address>
                </div>
                
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Shipping Address</h6>
                    <address>
                        {{ $order->shipping_address ?: '—' }}<br>
                        @if($order->shipping_city || $order->shipping_state || $order->shipping_postcode)
                            {{ $order->shipping_city }}{{ $order->shipping_city && $order->shipping_state ? ', ' : '' }}{{ $order->shipping_state }}<br>
                            {{ $order->shipping_postcode }}<br>
                        @endif
                        {{ $order->shipping_country ?: '' }}
                    </address>
                </div>
            </div>
            
            @if($order->line_items && count($order->line_items) > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->line_items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['name'] ?? '—' }}</td>
                                    <td>{{ $item['quantity'] ?? 1 }}</td>
                                    <td class="text-end">{{ $order->currency }} {{ number_format($item['price'] ?? 0, 2) }}</td>
                                    <td class="text-end">{{ $order->currency }} {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

