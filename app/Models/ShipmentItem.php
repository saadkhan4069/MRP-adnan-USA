<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'product_id',
        'product_code',
        'product_unit',
        'net_unit_cost',
        'discount',
        'subtotal',
        'qty',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
