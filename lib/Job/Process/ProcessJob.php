<?php

namespace Phpactor\Extension\Maestro\Job\Process;

use Amp\Process\Process;
use Amp\Process\ProcessInputStream;
use Amp\Promise;
use Phpactor\Extension\Maestro\Job\Process\Exception\ProcessFailed;
use Phpactor\Extension\Maestro\Model\Console\Console;
use Phpactor\Extension\Maestro\Model\Job\Job;

class ProcessJob implements Job
{
    private $command;
    private $cwd;
    private $console;

    public function __construct(string $command, string $cwd, string $console)
    {
        $this->command = $command;
        $this->cwd = $cwd;
        $this->console = $console;
    }

    public function cwd(): string
    {
        return $this->cwd;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function console(): string
    {
        return $this->console;
    }

    public function handler(): string
    {
        return ProcessHandler::class;
    }
}
