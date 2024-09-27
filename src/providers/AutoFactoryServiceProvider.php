<?php

namespace Asif\AutoFactory\Providers;

use Illuminate\Support\ServiceProvider;
use Asif\AutoFactory\Console\Commands\GenerateFactoryCommand;
use Asif\AutoFactory\Contracts\FieldMapperInterface;
use Asif\AutoFactory\Services\FakerFieldMapper;
use DB;
use Log;

class AutoFactoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FieldMapperInterface::class, FakerFieldMapper::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            DB::listen(function ($query) {
                Log::info(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });

            $this->commands([
                GenerateFactoryCommand::class,
            ]);
        }
    }
}
