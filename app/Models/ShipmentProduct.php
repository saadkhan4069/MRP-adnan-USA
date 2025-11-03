<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentProduct extends Model
{
    protected $guarded = [];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
