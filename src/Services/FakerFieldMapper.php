<?php

namespace Asif\AutoFactory\Services;

use Illuminate\Support\Facades\Schema;
use Asif\AutoFactory\Contracts\FieldMapperInterface;

class FakerFieldMapper implements FieldMapperInterface
{
    public function generateFactoryFields($table, $columns)
    {
        $fields = [];
        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);
            $fields[] = "'{$column}' => " . $this->getFakerValueForType($type, $column);
        }
        return implode(",\n\t\t\t", $fields);
    }

    private function getFakerValueForType($type, $column)
    {
        switch ($type) {
                // String types
            case 'string':
            case 'varchar':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return $this->getStringFaker($column);

            case 'char':
                return 'fake()->randomLetter';

                // Number types
            case 'int':
            case 'integer':
            case 'bigint':
            case 'smallint':
            case 'tinyint':
                return 'fake()->numberBetween(1, 1000)';

            case 'float':
            case 'double':
            case 'decimal':
                return 'fake()->randomFloat(2, 0, 1000)';

                // Boolean type
            case 'boolean':
                return 'fake()->boolean';

                // Date and time types
            case 'date':
                return 'fake()->date';
            case 'datetime':
            case 'timestamp':
                return 'fake()->dateTime';
            case 'time':
                return 'fake()->time';
            case 'year':
                return 'fake()->year';

                // Binary types
            case 'binary':
                return 'fake()->sha256';

                // UUID types
            case 'uuid':
                return 'fake()->uuid';

                // JSON / JSONB types
            case 'json':
            case 'jsonb':
                return 'fake()->randomElement([[], ["key" => "value"]])';

                // MongoDB specific types
            case 'objectid':
                return 'fake()->uuid';

                // Default
            default:
                return 'fake()->word';
        }
    }

    private function getStringFaker($column)
    {
        if (str_contains($column, 'email')) {
            return 'fake()->unique()->safeEmail';
        } elseif (str_contains($column, 'name')) {
            return 'fake()->name';
        } elseif (str_contains($column, 'url')) {
            return 'fake()->url';
        } elseif (str_contains($column, 'uuid')) {
            return 'fake()->uuid';
        } elseif (str_contains($column, 'phone')) {
            return 'fake()->phoneNumber';
        } elseif (str_contains($column, 'password')) {
            return "bcrypt('password')";
        } else {
            return 'fake()->word';
        }
    }
}
