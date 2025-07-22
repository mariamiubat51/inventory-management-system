<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id', 'invoice_no', 'purchase_date',
        'total_amount', 'paid_amount', 'due_amount',
        'payment_status', 'purchase_status', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
