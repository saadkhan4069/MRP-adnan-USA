<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',  // FEDEX, UPS, DHL etc.
        'po_no',
        'reference_no',
        'customer_id',
        'status',
        'ship_from_company',
        'ship_from_first_name',
        'ship_from_address_1',
        'ship_from_country',
        'ship_from_state',
        'ship_from_city',
        'ship_from_zipcode',
        'ship_from_contact',
        'ship_from_email',
        'ship_to_company',
        'ship_to_first_name',
        'ship_to_address_1',
        'ship_to_country',
        'ship_to_state',
        'ship_to_city',
        'ship_to_zipcode',
        'ship_to_contact',
        'ship_to_email',
        'currency_id',
        'exchange_rate',
        'service_code',
        'saturday_delivery',
        'signature_option',
        'bill_to',
        'ups_account',
        'incoterms',
        'contents_type',
        'declared_value_total',
        'order_tax_rate',
        'order_discount',
        'shipping_cost',
        'comments',
        'total_qty',
        'total_discount',
        'total_tax',
        'total_cost',
        'item',
        'order_tax',
        'grand_total',
        'paid_amount',
        'payment_status',
        'provider',
        'service_name',
        'payer',
        'account_number',
        'tracking_number',
        'label_format',
        'label_url',
        'invoice_url',
        'customs_docs_url',
        'rate_breakdown',
        'carrier_request',
        'carrier_response',
        'meta',

    ];

 

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function packages()
    {
        return $this->hasMany(ShipmentPackage::class);
    }

     public function customer()
    {
        // Agar aapki customers wali table ka model App\Models\Customer hai
        // aur foreign key 'customer_id' hai (default), to ye hi kaafi hai
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public function attachments()
{
    return $this->hasMany(\App\Models\ShipmentAttachment::class, 'shipment_id');
}

}
