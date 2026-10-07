<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pre_repair_checklist' => 'array',
    ];

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

    public function repairLogs()
    {
        return $this->hasMany(RepairLog::class)->latest(); // সর্বশেষ লগ আগে দেখাবে
    }
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
