<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpec extends Model
{
    use HasFactory;
    protected $casts = [
        'formula_date' => 'datetime',
    ];
     protected $fillable = [
         
        'product_id','density_lbs_per_gal','ph','brix','taste','appearance','batching_instructions','formula_date','process','yield_gallons'
    ];
}
