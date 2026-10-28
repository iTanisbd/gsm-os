<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'category',
        'brand',
        'model',
        'purchase_price',
        'selling_price',
        'stock',
        'low_stock_threshold',
        'sku',
        'note'
    ];

    // Relationship with Shop
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Check if stock is low
    public function getIsLowStockAttribute()
    {
        return $this->stock <= $this->low_stock_threshold;
    }
}
