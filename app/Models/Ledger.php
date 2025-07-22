<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $fillable = ['account_id', 'date', 'type', 'amount', 'description'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
