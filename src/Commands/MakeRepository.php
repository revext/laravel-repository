<?php

namespace Revext\Repository\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {modelClassName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new repository for the give model class';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->getArguments() === 0) {
            $this->error("Please provide a classname!");

            return 0;
        }

        $modelClassName = $this->argument('modelClassName');

        $repo_path = app_path('Repositories');

        if(!file_exists($repo_path)){
            mkdir($repo_path);
        }

        $repositoryInterfacePath = $repo_path . '/' .$modelClassName . 'Repository.php';
        $repositoryPath = $repo_path . '/' .$modelClassName . 'RepositoryEloquent.php';

        if(file_exists($repositoryInterfacePath)){
            $this->error($modelClassName . 'Repository already exists. Please delete previous before proceeding!');

            return 0;
        }
        if(file_exists($repositoryPath)){
            $this->error($modelClassName . 'RepositoryEloquent already exists. Please delete previous before proceeding!');

            return 0;
        }

        if(copy(__DIR__.'/Stubs/Repository.stub', $repositoryInterfacePath)){
            $this->replaceClassName($repositoryInterfacePath, $modelClassName);

            $this->info('Successfuly created ' . $modelClassName . 'Repository');
        }
        
        if(copy(__DIR__.'/Stubs/RepositoryEloquent.stub', $repositoryPath)){
            $this->replaceClassName($repositoryPath, $modelClassName);

            $this->info('Successfuly created ' . $modelClassName . 'RepositoryEloquent');
        }

        return 1;
    }

    public function replaceClassName($path, $className){
        $content = file_get_contents($path);

        $newContent = Str::of($content)->replace('{{modelClassName}}', $className);

        file_put_contents($path, $newContent);
    }
}
