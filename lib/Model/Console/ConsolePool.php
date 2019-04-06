<?php

namespace Phpactor\Extension\Maestro\Model;

class ConsolePool
{
    /**
     * @var array
     */
    private $consoles = [];

    public function get(string $name): Console
    {
        if (!isset($this->consoles[$name])) {
            $console = new Console($name);
            $this->consoles[$name] = $console;
        }

        return $this->consoles[$name];
    }

    /**
     * @return Console[]
     */
    public function all(): array
    {
        return $this->consoles;
    }
}
