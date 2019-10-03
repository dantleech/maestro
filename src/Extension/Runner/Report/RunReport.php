<?php

namespace Maestro\Extension\Runner\Report;

use Generator;
use Maestro\Extension\Report\Model\ConsoleReport;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\State;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class RunReport implements ConsoleReport
{
    /**
     * @var int
     */
    private $aggregationDepth;

    public function __construct(int $aggregationDepth = 1)
    {
        $this->aggregationDepth = $aggregationDepth;
    }

    public function title(): string
    {
        return 'Run Report';
    }

    public function description(): string
    {
        return 'Summary of all tasks executed during run';
    }

    public function render(OutputInterface $output, Graph $graph): void
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
        $table->setColumnMaxWidth(3, 30);
        $table->setColumnMaxWidth(4, 50);

        foreach ($graph->roots() as $root) {
            $packageNo = 0;
            foreach ($graph->dependentsFor($root->id()) as $packageNode) {
                $taskRows = $this->taskRows($graph, $packageNode->id());

                if ($packageNo++ > 0) {
                    $table->addRow(new TableSeparator());
                }

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
            '<options=bold;%s> %s nodes, %s pending %s succeeded, %s cancelled, %s failed </>',
            $this->resolveStatusColor($graph),
            $graph->nodes()->count(),
            $graph->nodes()->byState(State::IDLE())->count(),
            $graph->nodes()->byState(State::SUCCEEDED())->count(),
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
                    $this->stateIcon($taskNode),
                    ''
                ),
                $failure ? $failure->getMessage() : ''
            ];
        }
    }

    private function stateIcon(Node $taskNode)
    {
        if ($taskNode->state()->isDone()) {
            return '<info>✔</>';
        }
       
        if ($taskNode->state()->isFailed()) {
            return '<fg=red>✘</>';
        }
       
        if ($taskNode->state()->isCancelled()) {
            return '<comment>-</>';
        }
    }

    private function resolveStatusColor(Graph $graph): string
    {
        if ($graph->nodes()->byState(State::FAILED())->count()) {
            return 'fg=white;bg=red';
        }

        if ($graph->nodes()->byState(State::SUCCEEDED())->count() === $graph->nodes()->count()) {
            return 'fg=black;bg=green';
        }

        return 'fg=black;bg=yellow';
    }
}
