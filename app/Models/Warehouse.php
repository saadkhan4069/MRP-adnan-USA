<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable =[

        "name", "company", "phone", "email", "address","web", "is_active"
    ];

    public function product()
    {
    	return $this->hasMany('App\Models\Product');

    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('qty');
    }
}
