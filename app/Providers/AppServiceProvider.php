<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UsersRepositoryInterface;
use App\Repositories\UsersRepository;

use App\Interfaces\TypeDamageRepositoryInterface;
use App\Repositories\TypeDamageRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UsersRepositoryInterface::class, UsersRepository::class);

        $this->app->bind(TypeDamageRepositoryInterface::class,TypeDamageRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
