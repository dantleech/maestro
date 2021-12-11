<?php

namespace Maestro\Extension\Script\Task;

use Maestro\Library\Task\Task;

class ScriptTask implements Task
{
    /**
     * @var string
     */
    private $script;

    /**
     * @var string|null
     */
    private $workspace;

    public function __construct(string $script, string $workspace = null)
    {
        $this->script = $script;
        $this->workspace = $workspace;
    }

    public function description(): string
    {
        return sprintf('running %s', $this->script);
    }

    public function script(): string
    {
        return $this->script;
    }

    public function workspace(): ?string
    {
        return $this->workspace;
    }
}
