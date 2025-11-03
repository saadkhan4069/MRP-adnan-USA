<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_purchase_log extends Model
{
    protected $table = 'product_purchase_logs';
     public $timestamps = false; // 👈 This disables created_at and updated_at auto handling
    protected $fillable =[

        "id","purchase_id", "user_id", "notes","created_at","updated_at"
    ];
  
		public function user()
		{
		    return $this->belongsTo(User::class, 'user_id');
		}

		public function customer()
		{
		    return $this->belongsTo(Customer::class, 'user_id');

		}



}
