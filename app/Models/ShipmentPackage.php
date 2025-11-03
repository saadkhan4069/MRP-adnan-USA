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
        'weight',
        'length',
        'width',
        'height',
        'declared_value',
        'dimensions_note',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
