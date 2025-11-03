<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;

    /**   
     * Mass-assignable fields
     */   
    protected $fillable = [
        'name',
        'sku',
        'category',
        'default_unit',
        'default_price',
        'is_active',
        'vendor_id',               // NEW: link to vendors table
        'supplier_product_code',   // NEW: "Product Code" (PDF column)
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'is_active'     => 'boolean',
        'default_price' => 'decimal:4',
    ];

    /**
     * Default attributes
     */
    protected $attributes = [
        'default_unit'  => 'kg',
        'is_active'     => true,
    ];

    /**
     * Relationships
     */

    // Many-to-many with Product + extended pivot columns used in the catalog table
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot([
                'quantity',
                'unit',
                'unit_price',
                'wastage_pct',
                // PDF-style extras on pivot:
                'percent_w_w',
                'lbs_per_1k_gal',
                'gal_per_1k_gal',
                'sort_order',
                'product_code',
            ])
            ->withTimestamps()
            ->orderBy('product_raw_material.sort_order'); // show in recipe order
    }

    

    /**
     * Query scopes
     */

    // Quickly filter active materials
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Simple name/SKU search
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) return $query;
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }
}
