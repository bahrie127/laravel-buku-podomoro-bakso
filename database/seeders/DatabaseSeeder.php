<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create additional users for testing
        User::factory(2)->create();

        // Seed accounts and categories for all users
        $this->call([
            AccountSeeder::class,
            CategorySeeder::class,
        ]);

        // Create sample transactions
        $this->seedSampleTransactions();
    }

    private function seedSampleTransactions(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $accounts = $user->accounts;
            $categories = $user->categories;

            // Create some income transactions
            $incomeCategories = $categories->where('type', 'income');
            foreach ($incomeCategories->take(3) as $category) {
                \App\Models\Transaction::factory(2)->create([
                    'user_id' => $user->id,
                    'account_id' => $accounts->random()->id,
                    'category_id' => $category->id,
                    'type' => 'income',
                ]);
            }

            // Create some expense transactions
            $expenseCategories = $categories->where('type', 'expense');
            foreach ($expenseCategories->take(5) as $category) {
                \App\Models\Transaction::factory(3)->create([
                    'user_id' => $user->id,
                    'account_id' => $accounts->random()->id,
                    'category_id' => $category->id,
                    'type' => 'expense',
                ]);
            }

            // Create a transfer transaction
            if ($accounts->count() >= 2) {
                $fromAccount = $accounts->first();
                $toAccount = $accounts->skip(1)->first();

                \App\Models\Transaction::createTransfer(
                    $user,
                    $fromAccount,
                    $toAccount,
                    100000,
                    'Sample transfer between accounts'
                );
            }
        }
    }
}
