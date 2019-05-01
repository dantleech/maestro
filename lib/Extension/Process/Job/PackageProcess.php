<?php

namespace Maestro\Extension\Process\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queue;
use Maestro\Model\Package\PackageDefinition;

class PackageProcess implements Job
{
    /**
     * @var PackageDefinition
     */
    private $packageDefinition;

    /**
     * @var string
     */
    private $command;

    /**
     * @var Queue
     */
    private $queue;

    public function __construct(Queue $queue, PackageDefinition $packageDefinition, string $command)
    {
        $this->packageDefinition = $packageDefinition;
        $this->command = $command;
        $this->queue = $queue;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function packageDefinition(): PackageDefinition
    {
        return $this->packageDefinition;
    }

    public function queue(): Queue
    {
        return $this->queue;
    }
}
