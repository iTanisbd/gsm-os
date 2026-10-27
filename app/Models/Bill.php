<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $guarded = [];

    // অটোমেটিক ইউজার আইডি ট্র্যাকিং
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    // একটি বিলের অধীনে থাকা একাধিক আইটেম
    public function items()
    {
        return $this->hasMany(BillItem::class);
    }
}
