<?php

namespace Asif\AutoFactory\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class GenerateFactoryCommand extends Command
{
    protected $signature = 'make:autofactory {model}';
    protected $description = 'Generate a factory for a model';

    public function handle(QuestionHelper $helper)
    {
        $model = $this->argument('model');
        $modelClass = 'App\\Models\\' . $model;

        if (!class_exists($modelClass)) {
            $this->error("Model {$model} not found.");
            return;
        }

        $table = (new $modelClass)->getTable();
        $columns = Schema::getColumnListing($table);

        $factoryFields = $this->generateFactoryFields($table, $columns);

        $stub = file_get_contents(__DIR__ . '/../stubs/factory.stub');
        $factoryContent = str_replace(['{{ modelName }}', '{{ fields }}'], [$model, $factoryFields], $stub);

        $filePath = base_path("database/factories/{$model}Factory.php");

        if (File::exists($filePath)) {
            // Get console input for confirmation
            $question = new ConfirmationQuestion("The file {$model}Factory.php already exists. Do you want to overwrite it? (yes/no): ", false);

            if (!$helper->ask($this->input, $this->output, $question)) {
                $this->info('Aborting factory generation.');
                return;
            }
        }

        file_put_contents($filePath, $factoryContent);
        $this->info("Factory for {$model} generated successfully.");

        // Prompt user to generate dummy data
        $question = new ConfirmationQuestion("Do you want to generate dummy data using the factory? (yes/no): ", false);
        if ($helper->ask($this->input, $this->output, $question)) {
            // Ask how many records to generate
            $howManyQuestion = new Question('How many records would you like to generate?: ', 1);
            $recordCount = (int) $helper->ask($this->input, $this->output, $howManyQuestion);

            // Ensure a valid number
            if ($recordCount > 0) {
                $modelClass::factory($recordCount)->create();
                $this->info("Successfully generated {$recordCount} {$model} records.");
            } else {
                $this->info('Invalid number. Aborting record generation.');
            }
        }
    }

    private function generateFactoryFields($table, $columns)
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
                // Strings and Text
            case 'string':
            case 'varchar':
            case 'text':
            case 'mediumtext':
            case 'longtext':
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

            case 'char':
                return 'fake()->randomLetter';

                // Numbers
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

                // Boolean
            case 'boolean':
                return 'fake()->boolean';

                // Dates and Times
            case 'date':
                return 'fake()->date';
            case 'datetime':
            case 'timestamp':
                return 'fake()->dateTime';
            case 'time':
                return 'fake()->time';
            case 'year':
                return 'fake()->year';

                // Binary
            case 'binary':
                return 'fake()->sha256';

                // UUIDs
            case 'uuid':
                return 'fake()->uuid';

                // JSON / JSONB
            case 'json':
            case 'jsonb':
                return 'fake()->randomElement([[], ["key" => "value"]])';

                // MongoDB specific types
            case 'objectid':
                return 'fake()->uuid'; // Assuming ObjectId can be represented as UUID

                // Default
            default:
                return 'fake()->word';
        }
    }
}
