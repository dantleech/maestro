<?php

namespace Maestro\Library\Script;

class ScriptResult
{
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @var string
     */
    private $stdout;
    /**
     * @var string
     */
    private $stderr;

    public function __construct(int $exitCode, string $stdout, string $stderr)
    {
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function stderr(): string
    {
        return $this->stderr;
    }

    public function stdout(): string
    {
        return $this->stdout;
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }
}
