<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Task\Task;

class ScriptTask implements Task
{
    /**
     * @var string
     */
    private $script;

    public function __construct(string $script)
    {
        $this->script = $script;
    }

    public function description(): string
    {
        return $this->script;
    }

    public function script(): string
    {
        return $this->script;
    }
}
