<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Policies\TransactionPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFour();
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }
}
