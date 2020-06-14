<?php

namespace TodReacher\Generators\Commands;

use App\Models\Node\Node\Node;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ModelResourceCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model:resource';

    protected $signature = 'make:model:resource {model} {--service-path=}';

    protected $model;
    protected $modelPath;
    protected $modelShortName;
    protected $modelDirPath;
    protected $modelTraitsPath;

    protected $files;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create model resource';

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct($files);
    }

    public function handle()
    {
        $this->modelPath = str_replace("/", "\\", $this->argument("model"));

        $this->model          = new \ReflectionClass($this->modelPath);
        $this->modelShortName = $this->model->getShortName();
        $this->modelDirPath   = pathinfo($this->model->getFileName())["dirname"];

        $this->makeDirectory(app_path("Http/Resources"));
        $this->makeResource("model-resource.stub", "Resource");
        $this->makeResource("model-resources.stub", "Resources");
    }

    protected function makeResource($stubName, $type)
    {
        $stubPath = __DIR__ . '/../stubs/' . $stubName;
        $stub     = $this->files->get($stubPath);

        $serviceName = $this->option("service-path") ? $this->option("service-path") : $this->modelShortName;
        $servicePath = app_path("Http/Resources") . "/" . $serviceName;

        $this->makeDirectory($servicePath);

        $this
            ->replace($stub, "class", $this->modelShortName . $type)
            ->replace($stub, "namespace", str_replace("/", "\\", $serviceName));

        $filePath = $servicePath . "/" . $this->modelShortName . $type . ".php";
        $this->makeFile($filePath, $stub);
    }

    protected function makeFile($filePath, $stub)
    {
        if ($this->files->exists($filePath)) {
            $this->error("Exist: " . $filePath);
            return;
        }
        $this->files->put($filePath, $stub);
        $this->info("Ok: " . $filePath);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->exists($path)) {
            if (!$this->files->isDirectory($path)) {
                $this->files->makeDirectory($path, 0755, true, true);
            }
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
    }

    protected function replace(&$stub, $key, $value)
    {
        $value = str_replace('/', '\\', $value);
        $stub  = str_replace('{{' . $key . '}}', $value, $stub);
        return $this;
    }
}
