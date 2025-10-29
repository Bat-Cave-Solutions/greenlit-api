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
        $app = require __DIR__.'/../bootstrap/app.php';

        // Load .env.testing if present to customize env for tests; otherwise use defaults without warnings
        if (method_exists($app, 'loadEnvironmentFrom') && file_exists($app->basePath('.env.testing'))) {
            $app->loadEnvironmentFrom('.env.testing');
        }

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
