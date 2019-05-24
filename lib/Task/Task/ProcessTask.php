<?php

namespace Maestro\Task\Task;

use Maestro\Task\Task;

class ProcessTask implements Task
{
    /**
     * @var string
     */
    private $cmd;

    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct(string $cmd, string $workingDirectory)
    {
        $this->cmd = $cmd;
        $this->workingDirectory = $workingDirectory;
    }

    public function description(): string
    {
        return $this->cmd;
    }

    public function workingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function cmd(): string
    {
        return $this->cmd;
    }
}
