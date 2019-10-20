<?php

namespace Maestro\Library\Script;

use Maestro\Library\Artifact\Artifact;

class ScriptResult implements Artifact
{
    /**
     * @var int
     */
    public $exitCode;
    /**
     * @var string
     */
    public $stdout;
    /**
     * @var string
     */
    public $stderr;

    /**
     * @var string
     */
    public $script;

    public function __construct(string $script, int $exitCode, string $stdout, string $stderr)
    {
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->script = $script;
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

    public function serialize(): array
    {
        return [
            'exisCode' => $this->exitCode,
            'stderr' => $this->stderr,
            'stdout' => $this->stdout,
        ];
    }

    public function script(): string
    {
        return $this->script;
    }
}
