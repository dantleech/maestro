<?php

namespace Maestro\Extension\Maestro\Dumper;

use DateTimeImmutable;
use Maestro\Console\Dumper;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\Nodes;
use Maestro\Graph\State;
use Maestro\Graph\TaskResult;

class OverviewRenderer implements Dumper
{
    /**
     * @var DateTimeImmutable
     */
    private static $startTime;

    public function __construct()
    {
        if (static::$startTime === null) {
            self::$startTime = new DateTimeImmutable();
        }
    }

    public function dump(Graph $graph): string
    {
        $out = "\n";
        $hidden = 0;
        $done = 0;

        foreach ($graph->roots() as $rootNode) {
            foreach ($graph->dependentsFor($rootNode->id()) as $packageNode) {
                $nodes = $graph->descendantsFor($packageNode->id());

                if ($nodes->count() > 0 && $nodes->byState(State::DONE())->count() === $nodes->count()) {
                    $done++;
                    continue;
                }

                if ($nodes->byTaskResult(TaskResult::FAILURE())->count() === 0 && $nodes->byState(State::BUSY())->count() === 0) {
                    $hidden++;
                    continue;
                }

                $out .= $this->walkNode(
                    $nodes,
                    $packageNode
                );
            }
            $out .= "\n" . sprintf(
                '%s ... %s done, %s hidden. %s failed, %s successful tasks ',
                self::$startTime->diff(new DateTimeImmutable())->format('%hh %im %Ss'),
                $done,
                $hidden,
                $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count(),
                $graph->nodes()->byTaskResult(TaskResult::SUCCESS())->count()
            );


            foreach ($graph->nodes()->byTaskResult(TaskResult::FAILURE()) as $failedNode) {
                $out .= sprintf("  %s: %s\n", $failedNode->id(), $failedNode->task()->description());
            }
        }

        $out .= "\n" . sprintf('... and %s packages done, %s hidden', $done, $hidden);

        return $out;
    }

    private function walkNode(Nodes $nodes, Node $packageNode, int $depth = 0): string
    {
        $busyTasks= [];

        foreach ($nodes->byState(State::BUSY(), State::DONE()) as $node) {
            if ($node->taskResult()->is(TaskResult::SUCCESS())) {
                continue;
            }

            $busyTasks[] = sprintf(
                "\n           [\033[32m%s\033[0m] [\033[%sm%s\033[0m] %s",
                $node->label(),
                $this->stateColor($node->taskResult()),
                $node->taskResult()->toString(),
                $node->task()->description()
            );
        }

        $out = sprintf(
            "  %-2s/ %-2s %s [%s]%s\n",
            $nodes->byState(State::DONE())->count(),
            $nodes->count(),
            $this->successMark($nodes),
            "\033[34m" . $packageNode->label() . "\033[0m",
            implode("", $busyTasks),
        );

        return $out;
    }

    private function stateColor(TaskResult $taskResult): int
    {
        if ($taskResult->is(TaskResult::SUCCESS())) {
            return 36;
        }

        if ($taskResult->is(TaskResult::FAILURE())) {
            return 31;
        }

        if ($taskResult->is(TaskResult::PENDING())) {
            return 33;
        }

        return 0;
    }

    private function successMark(Nodes $nodes)
    {
        if ($nodes->byTaskResult(TaskResult::SUCCESS())->count() === $nodes->count()) {
            return  "\033[32m✔\033[0m";
        }

        if ($nodes->byTaskResult(TaskResult::FAILURE())->count()) {
            return  "\033[31m✘\033[0m";
        }

        return  "\033[35m↻\033[0m";
    }
}
