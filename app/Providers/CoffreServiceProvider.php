<?php

namespace App\Providers;

use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use Carbon\Laravel\ServiceProvider;

class CoffreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CleManagementService::class);
        $this->app->singleton(CoffreService::class);
    }
}
