<?php

namespace App\Providers;

use App\Dtos\DatabaseLayer\IDtoDatabase;
use App\Services\DatabaseDataRetriever;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
      $this->app->bind(DatabaseDataRetriever::class, function ($app, $parameters) {
        return new DatabaseDataRetriever($parameters['parameters']);
      });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
