<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    // সব ফিল্ড ইনসার্ট করার পারমিশন দেওয়া হলো
    protected $guarded = [];

    // এটি কোন বিলের আন্ডারে আছে তার রিলেশন
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
