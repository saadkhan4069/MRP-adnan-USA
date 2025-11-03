<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_Sale extends Model
{
	protected $table = 'product_sales';
    protected $fillable =[
"sale_id", "product_id", "product_batch_id", "variant_id", 'imei_number', "qty", "return_qty", "sale_unit_id", "net_unit_price", "discount", "tax_rate", "tax", "total", "is_packing", "is_delivered","topping_id","supplier_id","ets_date","eta_date","lt_date","moq","ship_term","ship_cost","batch_no"
    ];

     public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
     public function unit()
    {
        return $this->belongsTo(Unit::class,'sale_unit_id');
    }



}
