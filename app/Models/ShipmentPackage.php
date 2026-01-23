<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'packaging',
        'qty',
        'package_class',
        'package_nmfc',
        'commodity_name',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'dim_unit',
        'declared_value',
        'dimensions_note',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
