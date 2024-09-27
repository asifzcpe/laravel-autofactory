<?php

namespace Asif\AutoFactory\Providers;

use Illuminate\Support\ServiceProvider;
use Asif\AutoFactory\Console\Commands\GenerateFactoryCommand;
use Asif\AutoFactory\Contracts\FieldMapperInterface;
use Asif\AutoFactory\Services\FakerFieldMapper;

class AutoFactoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FieldMapperInterface::class, FakerFieldMapper::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateFactoryCommand::class,
            ]);
        }
    }
}
