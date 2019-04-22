<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Job\Job;
use Maestro\Model\Package\PackageWorkspace;

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

    public function handler(): string
    {
        return ProcessHandler::class;
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
