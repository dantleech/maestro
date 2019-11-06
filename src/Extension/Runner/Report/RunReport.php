<?php

namespace Maestro\Extension\Runner\Report;

use Maestro\Library\Graph\Nodes;
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
            '#',
            '✔',
            'node',
            'failure',
        ]);

        $packageNo = 0;
        $table->addRows($this->taskRows($graph, $graph->nodes()));
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

    private function taskRows(Graph $graph, Nodes $nodes): array
    {
        $rows = [];
        $index = 0;
        foreach ($nodes as $node) {
            $failure = $node->exception();
            $rows[] = [
                $index++,
                $this->stateIcon($node),
                sprintf("%s\n<fg=blue>%s</>", $node->id(), $node->task()->description()),
                $failure ? $failure->getMessage() : ''
            ];
            $rows[] = new TableSeparator();
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
