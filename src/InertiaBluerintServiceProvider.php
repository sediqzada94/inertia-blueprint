<?php

namespace Sediqzada\InertiaBlueprint;

use Illuminate\Support\ServiceProvider;
use Sediqzada\InertiaBlueprint\Commands\GenerateBlueprintPagesCommand;
use Sediqzada\InertiaBlueprint\Commands\PublishStubsCommand;

class InertiaBluerintServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/inertia-blueprint.php', 'inertia-blueprint');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../stubs' => resource_path('inertia-blueprint-stubs'),
            ], 'stubs');

            $this->commands([
                GenerateBlueprintPagesCommand::class,
                PublishStubsCommand::class,
            ]);
        }
    }
}
