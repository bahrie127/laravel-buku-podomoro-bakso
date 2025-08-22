<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'starting_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starting_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringRules(): HasMany
    {
        return $this->hasMany(RecurringRule::class);
    }

    public function getCurrentBalance(): float
    {
        $totalIncome = $this->transactions()
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = $this->transactions()
            ->where('type', 'expense')
            ->sum('amount');

        return $this->starting_balance + $totalIncome - $totalExpense;
    }
}
