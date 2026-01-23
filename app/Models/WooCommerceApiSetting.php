<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WooCommerceApiSetting extends Model
{
    use HasFactory;

    protected $table = 'woocommerce_api_settings';

    protected $fillable = [
        'platform_name',
        'website_url',
        'consumer_key',
        'consumer_secret',
        'is_active',
        'sync_interval',
        'last_sync_at',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(WooCommerceOrder::class, 'api_setting_id');
    }
}

