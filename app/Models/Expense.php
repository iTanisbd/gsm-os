<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'category',
        'amount',
        'expense_date',
        'note',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
