<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@admin.com')->firstOrFail();

        Account::firstOrCreate(
            ['name' => 'Banco Principal', 'user_id' => $user->id],
            [
                'type' => 'bank',
                'color' => '#3b82f6',
                'limit' => null,
            ]
        );

        Account::firstOrCreate(
            ['name' => 'Cartão Black', 'user_id' => $user->id],
            [
                'type' => 'credit_card',
                'color' => '#111827',
                'limit' => 15000.00,
                'closing_day' => 5,
                'due_day' => 10,
            ]
        );
    }
}
