<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Category;

class CategorizationService
{
    public function categorize(Transaction $transaction): bool
    {
        // 1. Exact Match History
        $lastTransaction = Transaction::where('user_id', $transaction->user_id)
            ->where('description', $transaction->description)
            ->whereNotNull('category_id')
            ->where('id', '!=', $transaction->id)
            ->latest('date')
            ->first();

        if ($lastTransaction) {
            $transaction->category_id = $lastTransaction->category_id;
            $transaction->save();
            return true;
        }

        // 2. Fuzzy / Contain Match (MVP: Simple 'LIKE')
        // This is expensive if we check ALL previous unique descriptions.
        // For MVP, maybe we skip this or hardcode some common ones if we had a dictionary.
        // But the PRD mentions "Regras manuais". 
        // Let's implement a simple "Contains" check if we had rules. 
        // Since we missed the table, let's skip for now or stick to History.

        return false;
    }
    
    public function runBatch(mixed $transactions): int
    {
        $count = 0;
        foreach ($transactions as $transaction) {
            if ($this->categorize($transaction)) {
                $count++;
            }
        }
        return $count;
    }
}
