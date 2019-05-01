<?php

namespace Maestro\Adapter\Amp\Job;

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
    private $consoleId;

    public function __construct(string $workingDirectory, string $command, string $consoleId)
    {
        $this->command = $command;
        $this->workingDirectory = $workingDirectory;
        $this->consoleId = $consoleId;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function workingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function consoleId()
    {
        return $this->consoleId;
    }
}
