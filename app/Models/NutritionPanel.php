<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionPanel extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id','serving_size_fl_oz','percent_juice','allergen','ingredients_statement',
        'calories','total_sugars_g','added_sugars_g'
    ];
}
