<?php

namespace TodReacher\Generators\Commands;

use App\Models\Node\Node\Node;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ModelServiceCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model:service';

    protected $signature = 'make:model:service {model} {--service-path=}';

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
    protected $description = 'Create model service';

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

        $this->makeDirectory(app_path("Services"));
        $this->makeService();
    }

    protected function makeService()
    {
        $stubPath = __DIR__ . '/../stubs/' . "model-service.stub";
        $stub     = $this->files->get($stubPath);

        $servicePath = app_path("Services") . "/" . $this->modelShortName;
        if ($this->option("service-path")) {
            $servicePath = app_path("Services") . "/" . $this->option("service-path");
        }

        $this->makeDirectory($servicePath);

        $serviceName = $this->option("service-path") ? $this->option("service-path") : $this->modelShortName;

        $this
            ->replace($stub, "class", $this->modelShortName)
            ->replace($stub, "namespace", $serviceName)
            ->replace($stub, "modelName", $this->modelShortName)
            ->replace($stub, "modelNamespace", $this->model->getNamespaceName() . "\\" . $this->modelShortName);

        $filePath = $servicePath . "/" . $this->modelShortName . "Service.php";
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
