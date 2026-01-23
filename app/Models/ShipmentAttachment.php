<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentAttachment extends Model
{
      use HasFactory;

    protected $table = 'shipment_attachments'; // agar aap single naam use kar rahe ho
    // agar table ka naam plural hai to 'shipment_attachments' likho

    protected $fillable = [
        'shipment_id',
        'title',
        'original_name',
        'filename',
        'path',
        'disk',
        'type',
        'mime',
        'size',
        'uploaded_by',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
