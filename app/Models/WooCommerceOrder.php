<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WooCommerceOrder extends Model
{
    use HasFactory;

    protected $table = 'woocommerce_orders';

    protected $fillable = [
        'api_setting_id',
        'platform_order_id',
        'order_number',
        'status',
        'currency',
        'total',
        'subtotal',
        'discount_total',
        'shipping_total',
        'tax_total',
        'customer_id',
        'customer_email',
        'customer_phone',
        'customer_first_name',
        'customer_last_name',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_postcode',
        'billing_country',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'order_date',
        'date_created',
        'date_modified',
        'date_completed',
        'date_paid',
        'customer_note',
        'order_notes',
        'line_items',
        'meta_data',
        'raw_data',
        'product_images',
        'is_synced',
        'synced_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'line_items' => 'array',
        'meta_data' => 'array',
        'raw_data' => 'array',
        'product_images' => 'array',
        'is_synced' => 'boolean',
        'order_date' => 'datetime',
        'date_created' => 'datetime',
        'date_modified' => 'datetime',
        'date_completed' => 'datetime',
        'date_paid' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function apiSetting()
    {
        return $this->belongsTo(WooCommerceApiSetting::class, 'api_setting_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

