<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'barcode', 
        'name',
        'description',
        'buying_price',          
        'selling_price',  
        'image',          
        'stock_quantity'
    ];

    public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}

}
