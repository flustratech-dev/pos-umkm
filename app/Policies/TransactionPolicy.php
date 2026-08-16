<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $user->store_id === $transaction->store_id;
    }

    public function verify(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function cancel(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
