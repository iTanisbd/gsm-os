<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $guarded = []; // সিকিউরিটির জন্য, যেন সব কলামে ডাটা ইনসার্ট করা যায়

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
