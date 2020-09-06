<?php

namespace Revext\Repository\Tests;

use Mockery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Revext\Repository\Utils\Repositories\PersonRepository;

class FilterTests extends BaseTestCase
{    
    protected $repository = null;

    public function setUp(): void {
        parent::setUp();

        DB::table('people')->truncate();
        $this->repository = $this->app->make(PersonRepository::class);
        $this->repository->boot();
        $this->repository->create(['name' => 'test1', 'email' => 'test@test.com']);
        $this->repository->create(['name' => 'test2', 'email' => 'test2@test.com']);
        $this->repository->create(['name' => 'test3', 'email' => 'test3@test.com']);
    }

    // Use annotation @test so that PHPUnit knows about the test
    public function testSearchWithSearchFilterForASingleValue()
    {
        $this->repository->setRequest(new Request(['filter' => ['search' => 'test1']]));
        $result = $this->repository->findAll();
        $this->assertEquals(1, $result->count());
        $this->assertEquals('test1', $result[0]->name);
    }

    // Use annotation @test so that PHPUnit knows about the test
    public function testSearchWithSearchFilterForAMultipleValues()
    {
        $this->repository->setRequest(new Request(['filter' => ['search' => 'test']]));
        $result = $this->repository->findAll();
        $this->assertEquals(3, $result->count());
    }
}