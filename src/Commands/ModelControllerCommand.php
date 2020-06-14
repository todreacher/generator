<?php

namespace TodReacher\Generators\Commands;

use App\Models\Node\Node\Node;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ModelControllerCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model:controller';

    protected $signature = 'make:model:controller {model} {--service-path=}';

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
    protected $description = 'Create model controller';

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

        $this->makeDirectory(app_path("Http/Controllers/Api/v1"));
        $this->makeController();
    }

    protected function makeController()
    {
        $stubPath = __DIR__ . '/../stubs/model-controller.stub';
        $stub     = $this->files->get($stubPath);

        $serviceName = $this->option("service-path") ? $this->option("service-path") : $this->modelShortName;
        $servicePath = app_path("Http/Controllers/Api/v1") . "/" . $serviceName;

        $this->makeDirectory($servicePath);

        $modelIndexValidate  = "App/Http/Requests/Api/v1/" . $serviceName . "/IndexRequest";
        $modelStoreValidate  = "App/Http/Requests/Api/v1/" . $serviceName . "/StoreRequest";
        $modelUpdateValidate = "App/Http/Requests/Api/v1/" . $serviceName . "/UpdateRequest";
        $modelResource       = "App/Http/Resources/" . $serviceName . "/" . $this->modelShortName . "Resource";
        $modelResources      = "App/Http/Resources/" . $serviceName . "/" . $this->modelShortName . "Resources";
        $modelService        = "App/Services/" . $serviceName . "/" . $this->modelShortName . "Service";

        $modelIndexRequestClass  = new \ReflectionClass(str_replace("/", "\\", "\\" . $modelIndexValidate));
        $modelStoreRequestClass  = new \ReflectionClass(str_replace("/", "\\", "\\" . $modelStoreValidate));
        $modelUpdateRequestClass = new \ReflectionClass(str_replace("/", "\\", "\\" . $modelUpdateValidate));
        $modelServiceClass       = new \ReflectionClass(str_replace("/", "\\", "\\" . $modelService));
        $modelResourceClass      = new \ReflectionClass(str_replace("/", "\\", "\\" . $modelResource));
        $modelResourcesClass     = new \ReflectionClass(str_replace("/", "\\", "\\" . $modelResources));

        $this
            ->replace($stub, "class", $this->modelShortName . "Controller")
            ->replace($stub, "namespace", $serviceName)
            ->replace($stub, "modelIndexRequest", $modelIndexValidate)
            ->replace($stub, "modelStoreRequest", $modelStoreValidate)
            ->replace($stub, "modelUpdateRequest", $modelUpdateValidate)
            ->replace($stub, "modelResource", $modelResource)
            ->replace($stub, "modelResources", $modelResources)
            ->replace($stub, "modelNamespace", $this->model->getNamespaceName() . "/" . $this->modelShortName)
            ->replace($stub, "modelService", $modelService)
            ->replace($stub, "modelClass", $this->modelShortName)
            ->replace($stub, "modelServiceClass", $modelServiceClass->getShortName())
            ->replace($stub, "modelResourceClass", $modelResourceClass->getShortName())
            ->replace($stub, "modelResourcesClass", $modelResourcesClass->getShortName());

        $filePath = $servicePath . "/" . $this->modelShortName . "Controller.php";
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

    protected function makeDirectory($path)
    {
        if (!$this->files->exists($path)) {
            if (!$this->files->isDirectory($path)) {
                $this->files->makeDirectory($path, 0755, true, true);
            }
        }
    }

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
