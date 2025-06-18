<?php

namespace MediaWebId\MakeModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module 
                            {name : Module name}
                            {--repository}
                            {--request}
                            {--resource}
                            {--controller}
                            {--frontend}
                            {--backend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate module structure including controller, request, resource, repository';

    /**
     * Execute the console command.
     */
    protected string $stubPath = __DIR__ . '/../resources/stubs'; // arahkan ke folder stubs relatif dari file ini

    protected function getStub(string $file): string
    {
        if (!File::exists($this->stubPath)) {
            throw new \Exception("Stub file not found: {$this->stubPath}");
        }

        return File::get("{$this->stubPath}/{$file}.stub");
    }


    protected function populateStub($stub, $data)
    {
        foreach ($data as $key => $value) {
            $stub = str_replace("{{ {$key} }}", $value, $stub);
            $stub = str_replace("{{ {$key} | lower }}", strtolower($value), $stub);
            $stub = str_replace("{{ {$key} | lowerPlural }}", Str::plural(strtolower($value)), $stub);
        }
        return $stub;
    }

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $this->info("Generating module: $name");

        if ($this->option('repository')) {
            $this->createRepository($name);
        }

        if ($this->option('request')) {
            $this->createRequest($name);
        }

        if ($this->option('controller')) {
            $this->createController($name,'Backend');
        }

        if ($this->option('resource')) {
            if ($this->option('frontend')) {
                $this->createResource($name, 'Frontend');
            }

            if ($this->option('backend')) {
                $this->createResource($name, 'Backend');
            }
        }
        $this->info("Module $name generated successfully.");
    }


    protected function createRepository($name)
    {
        $className = "{$name}Repository";
        $modelName = $name;
        $path = app_path("Http/Repositories/{$className}.php");

        if (File::exists($path)) {
            $this->warn("Repository $className already exists.");
            return;
        }

        $stub  = $this->getStub('repository');              // 👉 ambil file
        $code  = $this->populateStub($stub, [               // 👉 ganti placeholder
            'class' => $className,
            'model' => $modelName,
            'constant' => strtoupper($modelName),
            'tag'   => Str::plural(Str::snake($modelName)), // ex: package → packages
        ]);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $code);
        $this->info("✓ Repository created: {$className}");
    }

    protected function createRequest($name)
    {
        $className = "{$name}Request";
        $folder = app_path("Http/Requests/{$name}");
        $path = "$folder/$className.php";

        if (File::exists($path)) {
            $this->warn("Request $className already exists.");
            return;
        }

        $stub  = $this->getStub('request');
        $code  = $this->populateStub($stub, [
            'class' => $className,
            'model' => Str::lower($name), // FIX: lowercase untuk namespace
        ]);

        File::ensureDirectoryExists($folder);
        File::put($path, $code);
        $this->info("✓ Request created: {$className}");
    }

    protected function createResource($name, $namespace = 'Backend')
    {
        $className = "{$name}Resource";
        $folder = app_path("Http/Resources/{$namespace}");
        $path = "{$folder}/{$className}.php";

        if (File::exists($path)) {
            $this->warn("Resource $className already exists in $namespace.");
            return;
        }

        $modelClass = "App\\Models\\$name";
        $fillable = class_exists($modelClass)
            ? collect((new $modelClass)->getFillable())->map(fn($f) => "'$f' => \$this->$f,")->implode("\n")
            : "'id'";


        $stub = $this->getStub('resource');
        $code = $this->populateStub($stub, [
            'class' => $className,
            'namespace' => $namespace,
            'attributes' => $fillable,
        ]);

        File::ensureDirectoryExists($folder);
        File::put($path, $code);
        $this->info("✓ Resource created: {$namespace}/{$className}");
    }

    protected function createController($name, $namespace)
    {
        $className = $name . 'Controller';
        $folder = app_path("Http/Controllers/{$namespace}");
        $path = "$folder/{$className}.php";

        if (File::exists($path)) {
            $this->warn("Controller $className already exists.");
            return;
        }

        $stub = $this->getStub('controller');
        $code = $this->populateStub($stub, [
            'model' => $name,
            'namespace' => $namespace,
        ]);

        File::ensureDirectoryExists($folder);
        File::put($path, $code);
        $this->info("✓ Controller created: {$className}");
    }
    
}
