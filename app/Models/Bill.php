<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $guarded = [];

    // বিলটি কোন দোকানের
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // বিলটি কোন কাস্টমারের
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // বিলটি কোন ডিভাইসের কাজের জন্য
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
