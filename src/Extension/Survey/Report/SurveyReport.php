<?php

namespace Maestro\Extension\Survey\Report;

use Maestro\Library\Report\Report;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Survey\Survey;
use ReflectionClass;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SurveyReport implements Report
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function description(): string
    {
        return 'Shows all the information collected during any surveys';
    }

    public function render(Graph $graph): void
    {
        /** @var Node[] $nodes */
        $nodes = $graph->nodes()->byTaskClass(SurveyTask::class);
        $style = new SymfonyStyle(new ArrayInput([]), $this->output);

        foreach ($nodes as $node) {
            if (false === $node->artifacts()->has(Survey::class)) {
                continue;
            }

            $style->section($node->id());
            $survey = $node->artifacts()->get(Survey::class);
            assert($survey instanceof Survey);

            foreach ($survey as $surveyResult) {
                $table = new Table($this->output);
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
                $this->output->write(PHP_EOL);
            }
        }
    }
}
