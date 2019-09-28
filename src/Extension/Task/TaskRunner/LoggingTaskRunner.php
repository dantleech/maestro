<?php

namespace Maestro\Extension\Task\TaskRunner;

use Amp\Promise;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\TaskRunner;
use Psr\Log\LoggerInterface;

class LoggingTaskRunner implements TaskRunner
{
    /**
     * @var TaskRunner
     */
    private $innerTaskRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(TaskRunner $innerTaskRunner, LoggerInterface $logger)
    {
        $this->innerTaskRunner = $innerTaskRunner;
        $this->logger = $logger;
    }

    public function run(Task $task, Artifacts $artifacts): Promise
    {
        $context = [];
        if ($artifacts->has(Package::class)) {
            $package = $artifacts->get(Package::class);
            assert($package instanceof Package);
            $context['package'] = $package->name();
        }

        $this->logger->info('I\'m ' . $task->description(), $context);

        return $this->innerTaskRunner->run($task, $artifacts);
    }
}
