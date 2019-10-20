<?php

namespace Maestro\Extension\Runner\Report;

use Maestro\Extension\Runner\Task\PackageTask;
use Maestro\Library\Report\Report;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\State;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class RunReport implements Report
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function render(Graph $graph): void
    {
        $table = new Table($this->output);
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

        $packageNo = 0;
        foreach ($graph->nodes()->byTaskClass(PackageTask::class) as $packageNode) {
            $taskRows = $this->taskRows($graph, $packageNode->id());

            if ($packageNo++ > 0) {
                $table->addRow(new TableSeparator());
            }

            foreach ($taskRows as $index => $taskRow) {
                if ($index === 0) {
                    $table->addRow(array_merge([
                        $this->buildPackageRow($packageNode, count($taskRows)),
                    ], $taskRow));
                    continue;
                }

                $table->addRow($taskRow);
            }
        }
        $table->render();
        $this->output->writeln(sprintf(
            '<options=bold;%s> %s nodes, %s pending %s succeeded, %s cancelled, %s failed </>',
            $this->resolveStatusColor($graph),
            $graph->nodes()->count(),
            $graph->nodes()->byState(State::IDLE())->count(),
            $graph->nodes()->byState(State::SUCCEEDED())->count(),
            $graph->nodes()->byState(State::CANCELLED())->count(),
            $graph->nodes()->byState(State::FAILED())->count(),
        ));
    }

    private function buildPackageRow(Node $node, int $nbTasks): TableCell
    {
        return new TableCell($node->label(), [
            'rowspan' => $nbTasks
        ]);
    }

    private function taskRows(Graph $graph, string $packageId): array
    {
        $level = 0;
        $rows = [];
        foreach ($graph->descendantsForIncluding($packageId) as $taskNode) {
            if ($level++ && $taskNode->task() instanceof PackageTask) {
                break;
            }
            $failure = $taskNode->exception();
            $rows[] = [
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
        return $rows;
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
