<?php

namespace Asif\AutoFactory\Services;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class DummyDataGenerator
{
    public function generate($modelClass, $count, OutputInterface $output)
    {
        if (class_exists($modelClass)) {
            $progressBar = new ProgressBar($output, $count);
            $progressBar->start();

            for ($i = 0; $i < $count; $i++) {
                $modelClass::factory()->create();
                $progressBar->advance();
            }

            $progressBar->finish();
            $output->writeln('');
        }
    }
}
