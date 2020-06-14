<?php

namespace TodReacher\Generators\Commands;

use App\Models\Node\Node\Node;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ModelRequestCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model:request';

    protected $signature = 'make:model:request {model} {--service-path=}';

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
    protected $description = 'Create model request';

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

        $this->makeDirectory(app_path("Http/Requests/Api/v1"));
        $this->makeRequest("model-index-request.stub", "Index");
        $this->makeRequest("model-store-request.stub", "Store");
        $this->makeRequest("model-update-request.stub", "Update");
    }

    protected function makeRequest($stubName, $typeName)
    {
        $stubPath = __DIR__ . '/../stubs/' . $stubName;
        $stub     = $this->files->get($stubPath);

        $serviceName = $this->option("service-path") ? $this->option("service-path") : $this->modelShortName;
        $servicePath = app_path("Http/Requests/Api/v1") . "/" . $serviceName;

        $this->makeDirectory($servicePath);

        if (!$this->files->exists($this->modelDirPath . "/Traits/Validate/" . $this->modelShortName . $typeName . "Validate.php")) {
            $this->error("Error! " . $this->modelShortName . $typeName . "Validate.php" . " not found");
            return;
        }

        $this
            ->replace($stub, "classValidate", $this->modelShortName . $typeName . "Validate")
            ->replace($stub, "namespaceValidate",
                $this->model->getNamespaceName() . "\\Traits\\Validate\\" . $this->modelShortName . $typeName . "Validate")
            ->replace($stub, "namespace", str_replace("/", "\\", $serviceName));

        $filePath = $servicePath . "/" . $typeName . "Request.php";
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
