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
     * @var PackageDefinition
     */
    private $package;

    /**
     * @var string
     */
    private $command;

    public function __construct(PackageDefinition $package, string $command)
    {
        $this->package = $package;
        $this->command = $command;
    }

    public function handler(): string
    {
        return ProcessHandler::class;
    }

    public function package(): PackageDefinition
    {
        return $this->package;
    }

    public function command(): string
    {
        return $this->command;
    }
}
