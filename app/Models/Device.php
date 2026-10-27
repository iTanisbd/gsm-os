<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Device extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pre_repair_checklist' => 'array',
        'terms' => 'array',
        'reported_issues' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }

            if (empty($model->tracking_code)) {
                $model->tracking_code = 'DEV-' . strtoupper(Str::random(7));
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
        return $this->hasMany(RepairLog::class)->latest();
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function getRouteKeyName()
    {
        return 'tracking_code';
    }
}
