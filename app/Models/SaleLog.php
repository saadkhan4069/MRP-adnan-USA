<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleLog extends Model
{
    use HasFactory;
     protected $table = 'salelogs';
     protected $fillable = ['sale_id','user_id','notes'];
    protected $casts = ['notes' => 'array'];

    public function sale()
    {
        return $this->belongsTo(\App\Models\Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
