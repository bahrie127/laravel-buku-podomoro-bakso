<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Income categories
            $salaryCategory = Category::create([
                'user_id' => $user->id,
                'name' => 'Salary',
                'type' => 'income',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Base Salary',
                'type' => 'income',
                'parent_id' => $salaryCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Overtime',
                'type' => 'income',
                'parent_id' => $salaryCategory->id,
            ]);

            $businessCategory = Category::create([
                'user_id' => $user->id,
                'name' => 'Business',
                'type' => 'income',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Freelance',
                'type' => 'income',
                'parent_id' => $businessCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Investment',
                'type' => 'income',
            ]);

            // Expense categories
            $foodCategory = Category::create([
                'user_id' => $user->id,
                'name' => 'Food & Dining',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Restaurant',
                'type' => 'expense',
                'parent_id' => $foodCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Groceries',
                'type' => 'expense',
                'parent_id' => $foodCategory->id,
            ]);

            $transportCategory = Category::create([
                'user_id' => $user->id,
                'name' => 'Transportation',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Public Transport',
                'type' => 'expense',
                'parent_id' => $transportCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Fuel',
                'type' => 'expense',
                'parent_id' => $transportCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Bills & Utilities',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Entertainment',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Healthcare',
                'type' => 'expense',
            ]);

            // Transfer categories (auto-created by Transaction model when needed)
            Category::create([
                'user_id' => $user->id,
                'name' => 'Transfer',
                'type' => 'income',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Transfer',
                'type' => 'expense',
            ]);
        }
    }
}
