<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringRule extends Model
{
    /** @use HasFactory<\Database\Factories\RecurringRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'account_id',
        'category_id',
        'amount',
        'frequency',
        'start_date',
        'end_date',
        'next_run_date',
        'note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_run_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function createTransaction(): Transaction
    {
        $transaction = Transaction::create([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'type' => $this->type,
            'date' => $this->next_run_date,
            'amount' => $this->amount,
            'note' => $this->note . ' (Recurring)',
        ]);

        $this->updateNextRunDate();

        return $transaction;
    }

    public function updateNextRunDate(): void
    {
        $nextDate = Carbon::parse($this->next_run_date);

        switch ($this->frequency) {
            case 'daily':
                $nextDate->addDay();
                break;
            case 'weekly':
                $nextDate->addWeek();
                break;
            case 'monthly':
                $nextDate->addMonth();
                break;
        }

        // Check if the next date exceeds the end date
        if ($this->end_date && $nextDate->gt($this->end_date)) {
            $this->update(['is_active' => false]);
        } else {
            $this->update(['next_run_date' => $nextDate]);
        }
    }

    public function shouldRun(): bool
    {
        return $this->is_active &&
            $this->next_run_date->lte(now()) &&
            (!$this->end_date || $this->next_run_date->lte($this->end_date));
    }
}
