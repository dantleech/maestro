<?php

namespace Maestro\Extension\Process\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queue;
use Maestro\Model\Package\PackageDefinition;

class Checkout implements Job
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

    /**
     * @var string|null
     */
    private $url;

    public function __construct(
        Queue $queue,
        PackageDefinition $packageDefinition,
        string $url = null,
        bool $purge = false
    ) {
        $this->packageDefinition = $packageDefinition;
        $this->queue = $queue;
        $this->purge = $purge;
        $this->url = $url;
    }

    public function packageDefinition(): PackageDefinition
    {
        return $this->packageDefinition;
    }

    public function queue(): Queue
    {
        return $this->queue;
    }

    public function purge(): bool
    {
        return $this->purge;
    }

    public function url(): ?string
    {
        return $this->url;
    }
}
