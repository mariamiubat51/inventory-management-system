<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; //one-to-many Relation

class Account extends Model
{
    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'initial_balance',   
        'total_balance',
        'note',
    ];

    /**
     * Get all ledger entries for this account.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(Ledger::class);  // It means: "One account has many ledger entries."
    }
}
