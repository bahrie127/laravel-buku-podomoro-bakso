<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;

class TestBookkeepingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookkeeping:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the bookkeeping system functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Bookkeeping System...');

        // Get the test user
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->error('Test user not found. Please run the seeders first.');
            return 1;
        }

        $this->info("Testing with user: {$user->name} ({$user->email})");

        // Display accounts
        $this->info("\n=== ACCOUNTS ===");
        $accounts = $user->accounts;

        foreach ($accounts as $account) {
            $balance = $account->getCurrentBalance();
            $this->line("• {$account->name} ({$account->type}): Rp " . number_format($balance, 0, ',', '.'));
        }

        // Display categories
        $this->info("\n=== CATEGORIES ===");
        $this->info("Income Categories:");
        $incomeCategories = $user->categories()->where('type', 'income')->whereNull('parent_id')->get();
        foreach ($incomeCategories as $category) {
            $this->line("• {$category->name}");
            $children = $category->children;
            foreach ($children as $child) {
                $this->line("  - {$child->name}");
            }
        }

        $this->info("\nExpense Categories:");
        $expenseCategories = $user->categories()->where('type', 'expense')->whereNull('parent_id')->get();
        foreach ($expenseCategories as $category) {
            $this->line("• {$category->name}");
            $children = $category->children;
            foreach ($children as $child) {
                $this->line("  - {$child->name}");
            }
        }

        // Display recent transactions
        $this->info("\n=== RECENT TRANSACTIONS ===");
        $transactions = $user->transactions()
            ->with(['account', 'category'])
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        foreach ($transactions as $transaction) {
            $type = $transaction->type === 'income' ? '+' : '-';
            $amount = number_format($transaction->amount, 0, ',', '.');
            $transfer = $transaction->isTransfer() ? ' [TRANSFER]' : '';

            $this->line(
                "{$transaction->date->format('Y-m-d')} | " .
                    "{$type}Rp {$amount} | " .
                    "{$transaction->account->name} | " .
                    "{$transaction->category->name}" .
                    $transfer
            );
        }

        // Test transfer functionality
        $this->info("\n=== TESTING TRANSFER ===");
        if ($accounts->count() >= 2) {
            $fromAccount = $accounts->first();
            $toAccount = $accounts->skip(1)->first();

            $this->info("Creating transfer from {$fromAccount->name} to {$toAccount->name}...");

            $balanceBefore1 = $fromAccount->getCurrentBalance();
            $balanceBefore2 = $toAccount->getCurrentBalance();

            [$expenseTransaction, $incomeTransaction] = Transaction::createTransfer(
                $user,
                $fromAccount,
                $toAccount,
                50000,
                'Test transfer via command'
            );

            $balanceAfter1 = $fromAccount->fresh()->getCurrentBalance();
            $balanceAfter2 = $toAccount->fresh()->getCurrentBalance();

            $this->info("✓ Transfer completed!");
            $this->line("  {$fromAccount->name}: Rp " . number_format($balanceBefore1, 0, ',', '.') . " → Rp " . number_format($balanceAfter1, 0, ',', '.'));
            $this->line("  {$toAccount->name}: Rp " . number_format($balanceBefore2, 0, ',', '.') . " → Rp " . number_format($balanceAfter2, 0, ',', '.'));
            $this->line("  Transfer Group ID: {$expenseTransaction->transfer_group_id}");
        }

        $this->info("\n✅ Bookkeeping system test completed successfully!");

        return 0;
    }
}
