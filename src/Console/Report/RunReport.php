<?php

namespace Maestro\Console\Report;

use Generator;
use Maestro\Graph\Graph;
use Maestro\Graph\TaskResult;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\OutputInterface;

class RunReport
{
    /**
     * @var int
     */
    private $aggregationDepth;

    public function __construct(int $aggregationDepth = 1)
    {
        $this->aggregationDepth = $aggregationDepth;
    }

    public function render(OutputInterface $output, Graph $graph)
    {
        $table = new Table($output);
        $table->setHeaders([
            'package',
            'label',
            'action',
            '✔'
        ]);
        $table->setColumnMaxWidth(0, 30);
        $table->setColumnMaxWidth(1, 30);
        $table->setColumnMaxWidth(2, 30);
        $table->setColumnMaxWidth(3, 30);
        $table->setColumnMaxWidth(4, 30);

        foreach ($graph->roots() as $root) {
            foreach ($graph->dependentsFor($root->id()) as $packageNode) {
                $taskRows = $this->taskRows($graph, $packageNode->id());

                foreach ($taskRows as $index => $taskRow) {
                    if ($index === 0) {
                        $table->addRow(array_merge([
                            $this->buildPackageRow($graph, $packageNode->id()),
                        ], $taskRow));
                        continue;
                    }

                    $table->addRow($taskRow);
                }
            }
        }
        $table->render();
        $output->writeln(sprintf(
            '<options=bold;bg=%s;fg=white> %s tasks, %s failed, %s succeeded </>',
            $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count() ? 'red' : 'green',
            $graph->nodes()->byTaskResult(TaskResult::FAILURE(), TaskResult::SUCCESS())->count(),
            $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count(),
            $graph->nodes()->byTaskResult(TaskResult::SUCCESS())->count(),
        ));
    }

    private function buildPackageRow(Graph $graph, string $packageId): TableCell
    {
        return new TableCell($packageId, [
            'rowspan' => count($graph->descendantsFor($packageId))
        ]);
    }

    private function taskRows(Graph $graph, string $packageId): Generator
    {
        foreach ($graph->descendantsFor($packageId) as $taskNode) {
            $taskFailure = $taskNode->taskFailure();
            yield [
                $taskNode->label(),
                $taskNode->task()->description(),
                sprintf(
                    '%s %s',
                    $taskNode->taskResult()->isSuccess() ? '<info>✔</>' : '<fg=red>✘</>',
                    $taskFailure ? $taskFailure->getMessage() : ''
                )
            ];
        }
    }
}
