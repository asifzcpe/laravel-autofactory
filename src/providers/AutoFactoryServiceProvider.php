<?php

namespace Asif\AutoFactory\Providers;

use Illuminate\Support\ServiceProvider;
use Asif\AutoFactory\Commands\GenerateFactoryCommand;

class AutoFactoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            GenerateFactoryCommand::class,
        ]);
    }

    public function boot()
    {
        // You can add any boot-related logic here if needed
    }
}
