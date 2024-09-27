<?php

namespace Asif\AutoFactory\Console\Commands;

use Illuminate\Console\Command;
use Asif\AutoFactory\Services\FactoryGenerator;
use Asif\AutoFactory\Services\DummyDataGenerator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class GenerateFactoryCommand extends Command
{
    protected $signature = 'make:autofactory {model}';
    protected $description = 'Generate a factory for a model';
    protected $modelNamespace;

    public function __construct(
        private FactoryGenerator $factoryGenerator,
        private DummyDataGenerator $dummyDataGenerator
    ) {
        parent::__construct();
        $this->modelNamespace = env('AUTOFACTORY_MODEL_NAMESPACE', 'App\\Models\\');
    }

    public function handle(QuestionHelper $helper)
    {
        $model = $this->argument('model');
        $modelClass = $this->modelNamespace . $model;

        if (!class_exists($modelClass)) {
            $this->error("Model {$model} not found.");
            return;
        }


        if ($this->factoryGenerator->factoryExists($model)) {
            $overwrite = new ConfirmationQuestion("Factory exists. Overwrite? (yes/no): ", false);
            if (!$helper->ask($this->input, $this->output, $overwrite)) {
                $this->info('Aborting factory generation.');
                return;
            }
        }

        $this->factoryGenerator->generate($model, $this->modelNamespace);
        $this->info("Factory for {$model} generated successfully.");

        $generateDummyData = new ConfirmationQuestion("Generate dummy data? (yes/no): ", false);
        if ($helper->ask($this->input, $this->output, $generateDummyData)) {
            $countQuestion = new Question('How many records?: ', 1);
            $recordCount = (int)$helper->ask($this->input, $this->output, $countQuestion);
            $this->dummyDataGenerator->generate($modelClass, $recordCount, $this->output);
        }
    }
}
