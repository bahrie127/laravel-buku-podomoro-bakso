<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
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
                'name' => 'Gaji',
                'type' => 'income',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Gaji Pokok',
                'type' => 'income',
                'parent_id' => $salaryCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Lembur',
                'type' => 'income',
                'parent_id' => $salaryCategory->id,
            ]);

            $businessCategory = Category::create([
                'user_id' => $user->id,
                'name' => 'Usaha',
                'type' => 'income',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Penjualan Bakso',
                'type' => 'income',
                'parent_id' => $businessCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Investasi',
                'type' => 'income',
            ]);

            // Expense categories
            $foodCategory = Category::create([
                'user_id' => $user->id,
                'name' => 'Makanan & Minuman',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Restoran',
                'type' => 'expense',
                'parent_id' => $foodCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Belanja Bahan',
                'type' => 'expense',
                'parent_id' => $foodCategory->id,
            ]);

            $transportCategory = Category::create([
                'user_id' => $user->id,
                'name' => 'Transportasi',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Angkutan Umum',
                'type' => 'expense',
                'parent_id' => $transportCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Bensin',
                'type' => 'expense',
                'parent_id' => $transportCategory->id,
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Tagihan & Utilitas',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Hiburan',
                'type' => 'expense',
            ]);

            Category::create([
                'user_id' => $user->id,
                'name' => 'Kesehatan',
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
