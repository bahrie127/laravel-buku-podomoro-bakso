<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'date',
        'amount',
        'note',
        'counterparty',
        'transfer_group_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
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

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function transferPartner(): ?Transaction
    {
        if (!$this->transfer_group_id) {
            return null;
        }

        return static::where('transfer_group_id', $this->transfer_group_id)
            ->where('id', '!=', $this->id)
            ->first();
    }

    public static function createTransfer(
        User $user,
        Account $fromAccount,
        Account $toAccount,
        float $amount,
        string $note = null,
        string $date = null
    ): array {
        $transferGroupId = Str::uuid();
        $date = $date ?? now()->toDateString();

        $expenseTransaction = static::create([
            'user_id' => $user->id,
            'account_id' => $fromAccount->id,
            'category_id' => static::getTransferCategoryId($user, 'expense'),
            'type' => 'expense',
            'date' => $date,
            'amount' => $amount,
            'note' => $note ?? "Transfer to {$toAccount->name}",
            'counterparty' => $toAccount->name,
            'transfer_group_id' => $transferGroupId,
        ]);

        $incomeTransaction = static::create([
            'user_id' => $user->id,
            'account_id' => $toAccount->id,
            'category_id' => static::getTransferCategoryId($user, 'income'),
            'type' => 'income',
            'date' => $date,
            'amount' => $amount,
            'note' => $note ?? "Transfer from {$fromAccount->name}",
            'counterparty' => $fromAccount->name,
            'transfer_group_id' => $transferGroupId,
        ]);

        return [$expenseTransaction, $incomeTransaction];
    }

    private static function getTransferCategoryId(User $user, string $type): int
    {
        return Category::firstOrCreate([
            'user_id' => $user->id,
            'name' => 'Transfer',
            'type' => $type,
        ])->id;
    }

    public function isTransfer(): bool
    {
        return !is_null($this->transfer_group_id);
    }
}
