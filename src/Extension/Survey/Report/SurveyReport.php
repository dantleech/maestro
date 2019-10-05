<?php

namespace Maestro\Extension\Survey\Report;

use Maestro\Extension\Report\Model\ConsoleReport;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Survey\Survey;
use ReflectionClass;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SurveyReport implements ConsoleReport
{
    public function title(): string
    {
        return 'Survey results';
    }

    public function description(): string
    {
        return 'Shows all the information collected during any surveys';
    }

    public function render(OutputInterface $output, Graph $graph): void
    {
        /** @var Node[] $nodes */
        $nodes = $graph->nodes()->byTaskClass(SurveyTask::class);
        $style = new SymfonyStyle(new ArrayInput([]), $output);

        foreach ($nodes as $node) {
            if (false === $node->artifacts()->has(Survey::class)) {
                continue;
            }

            $style->section($node->id());
            $survey = $node->artifacts()->get(Survey::class);
            assert($survey instanceof Survey);

            foreach ($survey as $surveyResult) {
                $table = new Table($output);
                $style->block(get_class($surveyResult));

                $reflection = new ReflectionClass($surveyResult);

                foreach ($reflection->getProperties() as $property) {
                    $property->setAccessible(true);
                    $table->addRow([
                        $property->getName(),
                        json_encode($property->getValue($surveyResult))
                    ]);
                }
                $table->render();
                $output->write(PHP_EOL);
            }
        }
    }
}
