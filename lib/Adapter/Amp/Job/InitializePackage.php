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

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $commands;

    public function __construct(
        Queue $queue,
        PackageDefinition $packageDefinition,
        string $url = null,
        bool $purge = false,
        array $commands = []
    )
    {
        $this->packageDefinition = $packageDefinition;
        $this->queue = $queue;
        $this->purge = $purge;
        $this->url = $url;
        $this->commands = $commands;
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

    public function url(): ?string
    {
        return $this->url;
    }

    public function commands(): array
    {
        return $this->commands;
    }
}
