<?php

namespace Tests;

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TokenAuthentication;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bu middleware'ler test ortamında gereksiz ve sorun çıkarabiliyor
        $this->withoutMiddleware([
            TokenAuthentication::class,
            SecurityHeaders::class,
        ]);
    }
}
