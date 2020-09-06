<?php

namespace Revext\Repository\Tests;

use Mockery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Application;
use Revext\Repository\Utils\Models\Person;
use Revext\Repository\Utils\Repositories\DogRepository;
use Revext\Repository\Utils\Repositories\PersonRepository;

class RepositoryQueryTests extends BaseTestCase
{    
    public function emptyDatabase()
    {   
        DB::table('people')->truncate();
    }

    public function testFindAllQueryWithValidFilter()
    {
        $this->emptyDatabase();

        $repository = $this->app->make(PersonRepository::class);

        $repository->create(['name' => 'test', 'email' => 'test@test.com']);
        $repository->create(['name' => 'asdf', 'email' => 'test2@test.com']);

        $repository->setRequest(new Request(['filter' => ['name' =>'asd']]));
        
        $result = $repository->findAll();

        $this->assertEquals(1, $result->count());
        $this->assertEquals('asdf', $result[0]->name);
    }
    
    public function testFindAllQueryWithInvalidFilter()
    {
        $this->expectException(\Spatie\QueryBuilder\Exceptions\InvalidFilterQuery::class);
        $this->emptyDatabase();

        $repository = $this->app->make(PersonRepository::class);

        $repository->create(['name' => 'test', 'email' => 'test@test.com']);
        $repository->create(['name' => 'asdf', 'email' => 'test2@test.com']);

        $repository->setRequest(new Request(['filter' => ['asdg' =>'asd'], 'append' => ['']]));
        
        $repository->findAll();
    }

    public function testFindAllQueryWithValidAppend()
    {
        $this->emptyDatabase();

        $repository = $this->app->make(PersonRepository::class);

        $repository->create(['name' => 'test', 'email' => 'test@test.com']);

        $repository->setRequest(new Request(['append' => ['namail']]));
        
        $result = $repository->findAll();

        $this->assertEquals('test test@test.com', $result[0]->namail);
    }

}