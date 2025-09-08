<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'barcode', 
        'name',
        'category_id',
        'unit_id',
        'description',
        'buying_price',          
        'selling_price',  
        'image',          
        'stock_quantity',
        'reorder_level'
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
    public function category() {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit() {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }
}
