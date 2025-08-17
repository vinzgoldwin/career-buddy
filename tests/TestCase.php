<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Env;
use Dotenv\Dotenv;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Load the .env.testing file if it exists and we're in the testing environment
        if (Env::get('APP_ENV') === 'testing') {
            // Use dirname(__DIR__) to get the base path instead of the base_path() helper
            $dotenv = Dotenv::createMutable(dirname(__DIR__), '.env.testing');
            $dotenv->load();
        }

        parent::setUp();
    }
}
