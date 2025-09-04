<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_no', 'sale_date', 'customer_id', 'subtotal',
        'discount', 'grand_total', 'paid_amount', 'due_amount',
        'payment_method', 'note'
    ];

    protected $casts = [
        'sale_date' => 'date',  // or 'datetime' if it has time component
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}
