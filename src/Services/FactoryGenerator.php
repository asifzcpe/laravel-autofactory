<?php

namespace Asif\AutoFactory\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Asif\AutoFactory\Contracts\FieldMapperInterface;

class FactoryGenerator
{
    private $fieldMapper;

    public function __construct(FieldMapperInterface $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
    }

    public function factoryExists($model): bool
    {
        $filePath = base_path("database/factories/{$model}Factory.php");
        return File::exists($filePath);
    }

    public function generate($model, $modelNamespace): void
    {
        $modelClass = $modelNamespace . $model;
        $table = (new $modelClass)->getTable();
        $columns = Schema::getColumnListing($table);
        $columnsWithoutPrimaryKey = array_diff($columns, [(new $modelClass)->getKeyName()]);
        $factoryFields = $this->fieldMapper->generateFactoryFields($table, $columnsWithoutPrimaryKey);

        $stub = file_get_contents(__DIR__ . '/../stubs/factory.stub');
        $factoryContent = str_replace(['{{ modelName }}', '{{ fields }}'], [$model, $factoryFields], $stub);

        File::put(base_path("database/factories/{$model}Factory.php"), $factoryContent);
    }
}
