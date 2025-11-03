<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuotation extends Model
{
    protected $table = 'product_quotation';
    protected $fillable =[
        "quotation_id", "product_id", "product_batch_id", "variant_id", "qty", "sale_unit_id", "net_unit_price", "discount", "tax_rate", "tax", "total","supplier_id","eta_date","ets_date","lt_date","moq","ship_cost", 
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class,'sale_unit_id');
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class,'product_batch_id');
    }
}
