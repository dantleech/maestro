<?php

namespace Maestro\Console\Report;

use Generator;
use Maestro\Graph\Graph;
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
            yield [
                $taskNode->taskResult()->isSuccess() ? '<info>✔</>' : '<fg=red>✘</>',
                $taskNode->id(),
            ];
        }
    }
}
