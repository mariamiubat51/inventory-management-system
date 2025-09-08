<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $table = 'product_units'; // explicitly set table
    protected $fillable = ['name', 'symbol', 'is_active'];
}
