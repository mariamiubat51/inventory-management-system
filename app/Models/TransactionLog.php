<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'related_id',
        'account_id',
        'amount',
        'type',
        'transaction_date',
        'description',
    ];

    // This will cast transaction_date to Carbon (DateTime)
    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    // Relationship to Account (assuming Account model exists)
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    
    // Type label
    public function getTypeLabelAttribute()
    {
        return strtolower($this->type) === 'debit' ? 'Debit' : 'Credit';
    }

    // Transaction type label
    public function getTransactionTypeLabelAttribute()
    {
        $type = strtolower($this->transaction_type);

        return match($type) {
            'purchase' => 'Purchases',
            'sale' => $this->related_module ?? 'Sale', // optional: detect POS
            'pos' => 'Sale (POS)',
            'expense' => 'Expense',
            default => ucfirst($this->transaction_type),
        };
    }
}
