<?php

namespace Maestro\Adapter\Amp\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queue;
use Maestro\Model\Package\PackageDefinition;

class InitializePackage implements Job
{
    /**
     * @var PackageDefinition
     */
    private $packageDefinition;

    /**
     * @var Queue
     */
    private $queue;

    public function __construct(Queue $queue, PackageDefinition $packageDefinition)
    {
        $this->packageDefinition = $packageDefinition;
        $this->queue = $queue;
    }

    public function packageDefinition(): PackageDefinition
    {
        return $this->packageDefinition;
    }

    public function handler(): string
    {
        return InitializePackageHandler::class;
    }

    public function queue(): Queue
    {
        return $this->queue;
    }
}
