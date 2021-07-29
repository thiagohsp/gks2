<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;


use App\Repository\AccountRepositoryInterface;
use App\Repository\BatchRepositoryInterface;
use App\Repository\BillRepositoryInterface;
use App\Repository\CustomerRepositoryInterface;
use App\Repository\EloquentRepositoryInterface;
use App\Repository\InvoiceRepositoryInterface;
use App\Repository\UserRepositoryInterface;

use App\Repository\Eloquent\AccountRepository;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Eloquent\BatchRepository;
use App\Repository\Eloquent\BillRepository;
use App\Repository\Eloquent\CustomerRepository;
use App\Repository\Eloquent\InvoiceRepository;
use App\Repository\Eloquent\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(BillRepositoryInterface::class, BillRepository::class);
        $this->app->bind(BatchRepositoryInterface::class, BatchRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
