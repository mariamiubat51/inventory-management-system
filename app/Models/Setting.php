<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{    
    protected $fillable = [
        'company_name',
        'logo',
        'address',
        'phone',
        'email',
        'timezone',
        'currency',
        'low_stock_alert',
        'default_unit',
    ];
}
