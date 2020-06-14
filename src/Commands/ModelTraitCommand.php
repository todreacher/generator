<?php

namespace TodReacher\Generators\Commands;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ModelTraitCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model:trait';

    protected $signature = 'make:model:trait {model} {--attribute} {--relationship} {--scope} {--index-validate} {--store-validate} {--update-validate} {--all}';

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
    protected $description = 'Create model trait';

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct($files);
    }

    public function handle()
    {
        $this->modelPath = str_replace("/", "\\", $this->argument("model"));

        $this->model           = new \ReflectionClass($this->modelPath);
        $this->modelShortName  = $this->model->getShortName();
        $this->modelDirPath    = pathinfo($this->model->getFileName())["dirname"];
        $this->modelTraitsPath = $this->modelDirPath . "/Traits";

        $this->makeDirectory($this->modelTraitsPath);

        if ($this->option("attribute") or $this->option("all")) {
            $this->makeTrait("model-trait-attribute.stub", "Attribute");
        }

        if ($this->option("relationship") or $this->option("all")) {
            $this->makeTrait("model-trait-relationship.stub", "Relationship");
        }

        if ($this->option("scope") or $this->option("all")) {
            $this->makeTrait("model-trait-scope.stub", "Scope");
        }

        if ($this->option("index-validate") or $this->option("all")) {
            $this->makeValidate("model-trait-index-validate.stub", "Index");
        }

        if ($this->option("store-validate") or $this->option("all")) {
            $this->makeValidate("model-trait-store-validate.stub", "Store");
        }

        if ($this->option("update-validate") or $this->option("all")) {
            $this->makeValidate("model-trait-update-validate.stub", "Update");
        }
    }

    protected function makeTrait($stub, $typeName)
    {
        $stubPath = __DIR__ . '/../stubs/' . $stub;
        $stub     = $this->files->get($stubPath);

        $traitDir = $this->modelTraitsPath . "/" . $typeName;
        $this->makeDirectory($traitDir);

        $this
            ->replace($stub, "class", $this->modelShortName)
            ->replace($stub, "namespace", $this->model->getNamespaceName() . "\\Traits\\" . $typeName);

        $filePath = $traitDir . "/" . $this->modelShortName . $typeName . ".php";
        $this->makeFile($filePath, $stub);
    }

    protected function makeValidate($stub, $typeName)
    {
        $stubPath = __DIR__ . '/../stubs/' . $stub;
        $stub     = $this->files->get($stubPath);

        $traitDir = $this->modelTraitsPath . "/Validate";
        $this->makeDirectory($traitDir);

        $this
            ->replace($stub, "class", $this->modelShortName)
            ->replace($stub, "namespace", $this->model->getNamespaceName() . "\\Traits\\Validate");

        $filePath = $traitDir . "/" . $this->modelShortName . $typeName . "Validate.php";
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
