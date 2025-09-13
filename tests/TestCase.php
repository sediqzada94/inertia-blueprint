<?php

namespace Sediqzada\InertiaBlueprint\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sediqzada\InertiaBlueprint\InertiaBluerintServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            InertiaBluerintServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup test environment if needed
    }
}
