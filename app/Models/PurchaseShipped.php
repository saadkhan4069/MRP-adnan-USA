<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseShipped extends Model
{
    use HasFactory;
    protected $table = 'PurchaseShippeds'; // 👈 Table ka naam yahan specify kia gaya
    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'product_id',
        'user_id',
        'tracking_number',
    ];

    // Relationships (if needed)
   
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function Courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

}
