@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Order #{{ $order->order_number }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('woocommerce.orders.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Order Information</h6>
                        
                        <div class="form-group">
                            <label>Order Number <span class="text-danger">*</span></label>
                            <input type="text" name="order_number" class="form-control" value="{{ $order->order_number }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="refunded" {{ $order->status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-control" required>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->code }}" {{ $order->currency == $currency->code ? 'selected' : '' }}>
                                        {{ $currency->code }} - {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Total Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="total" class="form-control" value="{{ $order->total }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Order Date</label>
                            <input type="datetime-local" name="order_date" class="form-control" 
                                   value="{{ $order->order_date ? $order->order_date->format('Y-m-d\TH:i') : '' }}">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Buyer Information</h6>
                        
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="customer_first_name" class="form-control" value="{{ $order->customer_first_name }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="customer_last_name" class="form-control" value="{{ $order->customer_last_name }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="customer_email" class="form-control" value="{{ $order->customer_email }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="customer_phone" class="form-control" value="{{ $order->customer_phone }}">
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Billing Address</h6>
                        
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="billing_address" class="form-control" rows="3">{{ $order->billing_address }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="billing_city" class="form-control" value="{{ $order->billing_city }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="billing_state" class="form-control" value="{{ $order->billing_state }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Postcode</label>
                                    <input type="text" name="billing_postcode" class="form-control" value="{{ $order->billing_postcode }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Country</label>
                                    <input type="text" name="billing_country" class="form-control" value="{{ $order->billing_country }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Shipping Address</h6>
                        
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="shipping_address" class="form-control" rows="3">{{ $order->shipping_address }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="shipping_city" class="form-control" value="{{ $order->shipping_city }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="shipping_state" class="form-control" value="{{ $order->shipping_state }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Postcode</label>
                                    <input type="text" name="shipping_postcode" class="form-control" value="{{ $order->shipping_postcode }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Country</label>
                                    <input type="text" name="shipping_country" class="form-control" value="{{ $order->shipping_country }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Payment Information</h6>
                        
                        <div class="form-group">
                            <label>Payment Method</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ $order->payment_method }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Method Title</label>
                            <input type="text" name="payment_method_title" class="form-control" value="{{ $order->payment_method_title }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Order
                    </button>
                    <a href="{{ route('woocommerce.orders.index') }}" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
@endpush

@endsection

