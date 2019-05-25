<?php

namespace Maestro\Script;

class ScriptResult
{
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @var string
     */
    private $lastStdout;
    /**
     * @var string
     */
    private $lastStderr;

    public function __construct(int $exitCode, string $lastStdout, string $lastStderr)
    {
        $this->exitCode = $exitCode;
        $this->lastStdout = $lastStdout;
        $this->lastStderr = $lastStderr;
    }

    public function lastStderr(): string
    {
        return $this->lastStderr;
    }

    public function lastStdout(): string
    {
        return $this->lastStdout;
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }
}
