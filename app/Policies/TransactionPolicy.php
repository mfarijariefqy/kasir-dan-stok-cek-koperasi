<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewOwn(User $user, Transaction $transaction): bool
    {
        if (! $user->isCustomer()) {
            return false;
        }

        $customer = $user->customerProfile;

        return $customer && $transaction->customer_id === $customer->id;
    }

    public function editQty(User $user, Transaction $transaction): bool
    {
        return $this->viewOwn($user, $transaction)
            && $transaction->isBelumLunas();
    }
}
