<?php

namespace Maestro\Console;

use Maestro\Console\Exception\DumperNotFound;
use RuntimeException;

class DumperRegistry
{
    private $dumpers = [];

    public function __construct(array $dumpers = [])
    {
        foreach ($dumpers as $name => $dumper) {
            $this->add($name, $dumper);
        }
    }

    public function get(string $name): Dumper
    {
        if (!isset($this->dumpers[$name])) {
            throw new DumperNotFound(sprintf(
                'Dumper not found "%s", known dumpers "%s"',
                $name,
                implode('", "', array_keys($this->dumpers))
            ));
        }

        return $this->dumpers[$name];
    }

    private function add(string $name, Dumper $dumper)
    {
        $this->dumpers[$name] = $dumper;
    }
}
