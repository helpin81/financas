<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@admin.com')->firstOrFail();
        $account = Account::where('user_id', $user->id)->where('type', 'bank')->first();
        $creditCard = Account::where('user_id', $user->id)->where('type', 'credit_card')->first();

        // Income - Salary
        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => null, // Maybe 'Salário' later
            'date' => Carbon::now()->startOfMonth()->addDays(4),
            'description' => 'Salário Mensal',
            'amount' => 5000.00,
            'type' => 'income',
            'status' => 'paid',
        ]);

        // Expenses
        $categories = Category::where('user_id', $user->id)->get();

        // Random expenses for this month
        foreach (range(1, 15) as $i) {
            $cat = $categories->random();
            $isCredit = rand(0, 1);
            $acc = $isCredit ? $creditCard : $account;
            
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $acc->id,
                'category_id' => $cat->id,
                'date' => Carbon::now()->subDays(rand(0, 30)),
                'description' => 'Compra em ' . $cat->name . ' ' . $i,
                'amount' => rand(50, 500) * -1,
                'type' => 'expense',
                'status' => 'paid',
            ]);
        }
    }
}
