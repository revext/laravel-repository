<?php

namespace Revext\Repository\Tests;

use Orchestra\Testbench\TestCase;

abstract class BaseTestCase extends TestCase
{    
    protected function setUp(): void
    {
        parent::setUp();

		$this->loadMigrationsFrom(realpath(__DIR__ . '/../utils/database/migrations'));

		$this->artisan('migrate')->run();
        
	}
	
	public function tearDown(): void{
		$this->artisan('migrate:rollback')->run();
	}

    // When testing inside of a Laravel installation, this is not needed
    protected function getPackageProviders($app)
    {
        return [
            'Revext\Repository\RepositoryServiceProvider',
            'Spatie\QueryBuilder\QueryBuilderServiceProvider'
        ];
    }
}