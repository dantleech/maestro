<?php

namespace Maestro\Extension\Runner\Report;

use Generator;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\State;
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
            '✔',
            ''
        ]);
        $table->setColumnMaxWidth(0, 30);
        $table->setColumnMaxWidth(1, 30);
        $table->setColumnMaxWidth(2, 30);
        $table->setColumnMaxWidth(3, 60);

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
            '<options=bold;bg=%s;fg=white> %s nodes, %s succeeded, %s cancelled, %s failed</>',
            $graph->nodes()->byState(State::FAILED())->count() ? 'red' : 'green',
            $graph->nodes()->count(),
            $graph->nodes()->byState(State::DONE())->count(),
            $graph->nodes()->byState(State::CANCELLED())->count(),
            $graph->nodes()->byState(State::FAILED())->count(),
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
            $failure = $taskNode->exception();
            yield [
                $taskNode->label(),
                $taskNode->task()->description(),
                sprintf(
                    '%s %s',
                    $taskNode->state()->isDone() ? '<info>✔</>' : '<fg=red>✘</>',
                    ''
                ),
                $failure ? $failure->getMessage() : ''
            ];
        }
    }
}
