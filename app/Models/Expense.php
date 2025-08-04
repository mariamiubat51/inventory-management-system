<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['date', 'title', 'category_id', 'amount', 'note', 'account_id'];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
