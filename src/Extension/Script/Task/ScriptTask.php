<?php

namespace Maestro\Extension\Script\Task;

use Maestro\Library\Task\Task;

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
        return sprintf('running %s', $this->script);
    }

    public function script(): string
    {
        return $this->script;
    }
}
