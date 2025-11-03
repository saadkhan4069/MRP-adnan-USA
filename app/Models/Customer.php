<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable =[
        "id","customer_group_id", "user_id", "name", "company_name",
        "email", "type", "phone_number", "tax_no", "address", "city",
        "state", "postal_code", "country","web", "points", "deposit", "expense", "wishlist", "is_active", "note"
    ];

    public function customerGroup()
    {
        return $this->belongsTo('App\Models\CustomerGroup');
    }

    // public function user()
    // {
    // 	return $this->belongsTo('App\Models\User');
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    

    public function discountPlans()
    {
        return $this->belongsToMany('App\Models\DiscountPlan', 'discount_plan_customers');
    }
}
