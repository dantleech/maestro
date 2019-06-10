<?php

namespace Maestro\Console;

use RuntimeException;

class DumperRegistry
{
    private $dumpers;

    public function __construct(array $dumpers = [])
    {
        $this->dumpers = $dumpers;
    }

    public function get(string $name): Dumper
    {
        if (!isset($this->dumpers[$name])) {
            throw new RuntimeException(sprintf(
                'Dumper not found "%s", known dumpers "%s"',
                $name, implode('", "', array_keys($this->dumpers))
            ));
        }

        return $this->dumpers[$name];
    }
}
