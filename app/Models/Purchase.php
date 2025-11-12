<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable =[

        "comments","po_no","system_po_no","signature","reference_no", "user_id", "customer_id", "warehouse_id","production_id", "supplier_id", "currency_id", "exchange_rate", "item", "total_qty", "total_discount", "total_tax", "total_cost", "order_tax_rate", "order_tax", "order_discount", "shipping_cost", "grand_total","paid_amount", "status", "payment_status", "document", "note", "ship_instruction", "created_at"
    ];

    public function supplier()
    {
    	return $this->belongsTo('App\Models\Supplier');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function warehouse()
    {
    	return $this->belongsTo('App\Models\Warehouse');
    }

    public function wproduction()
    {
    return $this->belongsTo('App\Models\Wproduction', 'production_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnPurchase::class,'purchase_id');
    }
     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'product_purchases')->withPivot('qty','moq','tax','tax_rate','discount','total','id','purchase_id','product_id','product_batch_id','variant_id','supplier_id','imei_number','qty','recieved','return_qty','purchase_unit_id','net_unit_cost','discount','tax_rate','tax','total','ets_date','eta_date','etd_date','created_at','updated_at'
);
    }

    public function getCreatedAtFormattedAttribute()
    {
        $dateFormat = GeneralSetting::first()->date_format;
        return Carbon::parse($this->attributes['created_at'])->format($dateFormat);
    }
}
