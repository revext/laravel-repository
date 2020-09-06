<?php

namespace Revext\Repository\Tests;

use Mockery;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Application;
use Revext\Repository\Utils\Models\Person;
use Revext\Repository\Exceptions\RepositoryException;
use Revext\Repository\Utils\Repositories\DogRepository;
use Revext\Repository\Utils\Repositories\PersonRepository;

class RepositoryTest extends BaseTestCase
{    
    protected $repoWithSD = null;
    protected $repoWithoutSD = null;

    public function setUp(): void {
        parent::setUp();
        $this->repoWithSD = $this->app->make(PersonRepository::class);
        $this->repoWithoutSD = $this->app->make(DogRepository::class);
    }

    public function emptyDatabase()
    {   
        DB::table('people')->truncate();
    }

    // Use annotation @test so that PHPUnit knows about the test
    public function testCreate()
    {
        $this->emptyDatabase();

        $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);
        $this->repoWithSD->create(['name' => 'test', 'email' => 'test2@test.com']);
        $this->repoWithSD->create(['name' => 'test', 'email' => 'test3@test.com']);
    
        $this->assertEquals(3, Person::count());
        $this->assertDatabaseHas('people', [
            'name' => 'test',
            'email' => 'test2@test.com',
        ]);
    }

    public function testUpdate()
    {        
        $this->emptyDatabase();

        $entity = $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);
        
        $this->repoWithSD->update($entity->id, ['name' => 'test2', 'email' => 'test2@test.com']);
    
        $this->assertDatabaseHas('people', [
            'name' => 'test2',
            'email' => 'test2@test.com',
        ]);
    }


    public function testUpdateAll()
    {        
        $this->emptyDatabase();

        $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);
        $this->repoWithSD->create(['name' => 'test', 'email' => 'test2@test.com']);
        $this->repoWithSD->create(['name' => 'test', 'email' => 'test3@test.com']);
        
        $this->repoWithSD->updateAll(['name' => 'test'], ['name' => 'test5']);
    
        $this->assertEquals(3, Person::where('name', 'test5')->count());
    }

    public function testFindById(){
        $this->emptyDatabase();

        $entity = $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);

        $this->assertEquals($entity->id, $this->repoWithSD->findById($entity->id)->id);
    }

    public function testFindByColumns(){
        $this->emptyDatabase();

        $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);
        $this->repoWithSD->create(['name' => 'chair', 'email' => 'test2@test.com']);
        $entity = $this->repoWithSD->create(['name' => 'test', 'email' => 'test3@test.com']);


        $this->assertEquals($entity->id, $this->repoWithSD->findById($entity->id)->id);
    }

    public function testFindFirst(){
        $this->emptyDatabase();

        $entityFirst = $this->repoWithSD->create(['name' => 'test', 'email' => 'test3@test.com']);
        $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);
        $this->repoWithSD->create(['name' => 'chair', 'email' => 'test2@test.com']);

        $this->assertEquals($entityFirst->id, $this->repoWithSD->findFirst($entityFirst->id)->id);
    }

    public function testFindAll(){
        $this->emptyDatabase();

        $entity = $this->repoWithSD->create(['name' => 'test', 'email' => 'test3@test.com']);
        $entity = $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);
        $entity = $this->repoWithSD->create(['name' => 'chair', 'email' => 'test2@test.com']);

        $this->assertEquals(3, count($this->repoWithSD->findAll()));
    }

    public function testDelete(){
        $this->emptyDatabase();

        $this->repoWithSD->create(['name' => 'test', 'email' => 'test3@test.com']);
        $this->repoWithSD->create(['name' => 'test', 'email' => 'test@test.com']);
        $entity = $this->repoWithSD->create(['name' => 'chair', 'email' => 'test2@test.com']);

        $this->repoWithSD->delete($entity->id);

        $this->assertEquals(2, count($this->repoWithSD->findAll()));
    }

    public function testRestoreWithSD(){
        $this->emptyDatabase();

        $entity = $this->repoWithSD->create(['name' => 'test', 'email' => 'test3@test.com']);
        $this->repoWithSD->delete($entity->id);
        $entity = $this->repoWithSD->restore($entity->id);

        $this->assertDatabaseHas('people', [
            'name' => 'test',
            'email' => 'test3@test.com',
            'deleted_at' => null
        ]);
    }

    public function testRestoreWithoutSD(){
        $this->expectException(RepositoryException::class);
        $this->emptyDatabase();

        $entity = $this->repoWithoutSD->create(['name' => 'test', 'email' => 'test3@test.com']);
        $this->repoWithoutSD->delete($entity->id);
        $this->repoWithoutSD->restore($entity->id);
    }

}