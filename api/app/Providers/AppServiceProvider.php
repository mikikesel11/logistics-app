<?php

namespace App\Providers;

use App\Domain\Crm\Repositories\CarrierRepository;
use App\Domain\Crm\Repositories\CustomerRepository;
use App\Domain\Crm\Repositories\EloquentCarrierRepository;
use App\Domain\Crm\Repositories\EloquentCustomerRepository;
use App\Domain\LoadBoard\InternalLoadBoardProvider;
use App\Domain\LoadBoard\LoadBoardProvider;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        CustomerRepository::class => EloquentCustomerRepository::class,
        CarrierRepository::class => EloquentCarrierRepository::class,
        // Swap for a DAT/Truckstop adapter once API credentials exist.
        LoadBoardProvider::class => InternalLoadBoardProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
