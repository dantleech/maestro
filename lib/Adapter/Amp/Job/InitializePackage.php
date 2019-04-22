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

    /**
     * @var bool
     */
    private $purge;

    public function __construct(Queue $queue, PackageDefinition $packageDefinition, bool $purge = false)
    {
        $this->packageDefinition = $packageDefinition;
        $this->queue = $queue;
        $this->purge = $purge;
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

    public function purge(): bool
    {
        return $this->purge;
    }
}
