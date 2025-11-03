<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{
    protected $table = 'product_purchases';
    protected $fillable =[

        "purchase_id", "product_id", "product_batch_id", "variant_id","supplier_id", "imei_number","ets_date","eta_date","etd_date","moq","ship_cost", "qty", "recieved", "return_qty", "purchase_unit_id", "net_unit_cost", "discount", "tax_rate", "tax", "total","ship_term"
    ];


    public function unit()
{
    return $this->belongsTo(Unit::class, 'purchase_unit_id');
}

	public function product()
	{
	    return $this->belongsTo(Product::class);
	}
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
   
   public function purchase_unit()
{
    return $this->belongsTo(Unit::class, 'purchase_unit_id');
}

}
