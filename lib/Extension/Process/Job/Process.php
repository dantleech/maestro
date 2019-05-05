<?php

namespace Maestro\Extension\Process\Job;

use Maestro\Model\Job\Job;

class Process implements Job
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var string
     */
    private $ttyId;

    public function __construct(string $workingDirectory, string $command, string $ttyId)
    {
        $this->command = $command;
        $this->workingDirectory = $workingDirectory;
        $this->ttyId = $ttyId;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function workingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function ttyId()
    {
        return $this->ttyId;
    }

    public function description(): string
    {
        return $this->command;
    }
}
