<?php

namespace Revext\Repository\Tests;

class MakeRepositoryTest extends BaseTestCase
{    
    public function setUp(): void {
        // $dir = '/var/www/revext-repository/vendor/orchestra/testbench-core/laravel/app/Repositories';
        // if(file_exists($dir)){
        //     $files = array_diff(scandir($dir), array('.','..'));
        //     foreach ($files as $file) {
        //      unlink("$dir/$file");
        //     }
        //     rmdir($dir);
        // }
    }

    public function testMakeRepositoryTest()
    {

        $this->artisan('make:repository Bird');
        // echo app_path('Repositories') .  '/BirdRepository';
        // /var/www/revext-repository/vendor/orchestra/testbench-core/laravel/app/Repositories/BirdRepository
        $this->assertEquals(file_exists(app_path('Repositories') .  '/BirdRepository.php'), true);
        $this->assertEquals(file_exists(app_path('Repositories') .  '/BirdRepositoryEloquent.php'), true);
    }
}