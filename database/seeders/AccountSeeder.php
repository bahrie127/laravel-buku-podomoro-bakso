<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create default accounts for each user
            Account::create([
                'user_id' => $user->id,
                'name' => 'Kas Tunai',
                'type' => 'cash',
                'starting_balance' => 500000,
                'is_active' => true,
            ]);

            Account::create([
                'user_id' => $user->id,
                'name' => 'BCA Tabungan',
                'type' => 'bank',
                'starting_balance' => 2500000,
                'is_active' => true,
            ]);

            Account::create([
                'user_id' => $user->id,
                'name' => 'Mandiri Tabungan',
                'type' => 'bank',
                'starting_balance' => 5000000,
                'is_active' => true,
            ]);

            Account::create([
                'user_id' => $user->id,
                'name' => 'Dana',
                'type' => 'ewallet',
                'starting_balance' => 250000,
                'is_active' => true,
            ]);

            Account::create([
                'user_id' => $user->id,
                'name' => 'GoPay',
                'type' => 'ewallet',
                'starting_balance' => 150000,
                'is_active' => true,
            ]);

            // Create some additional random accounts
            Account::factory(2)->create(['user_id' => $user->id]);
        }
    }
}
