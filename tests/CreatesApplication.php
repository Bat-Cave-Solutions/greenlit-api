<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = require __DIR__.'/../bootstrap/app.php';

        // Load .env.testing if present to customize env for tests; otherwise use defaults without warnings
        $envTestingPath = dirname(__DIR__).'/.env.testing';
        if (method_exists($app, 'loadEnvironmentFrom') && file_exists($envTestingPath)) {
            $app->loadEnvironmentFrom('.env.testing');
        }

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
