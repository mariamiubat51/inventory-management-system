<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'opening_amount', 'closing_amount', 'status',
        'total_sales', 'notes',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'date', 'date'); // link sales by date
    }
}
