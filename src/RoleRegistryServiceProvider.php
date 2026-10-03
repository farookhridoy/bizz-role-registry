<?php

namespace Bizzsol\RoleRegistry;

use Illuminate\Support\ServiceProvider;

class RoleRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/role-registry.php', 'role-registry');
        $this->app->singleton(RoleRegistry::class);
    }

    public function boot(): void
    {
        if (config('role-registry.load_migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }
}
