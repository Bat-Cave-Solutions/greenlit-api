<?php

namespace Tests\Unit;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_application_has_a_name_configured(): void
    {
        $this->assertNotEmpty(config('app.name'));
    }
}
