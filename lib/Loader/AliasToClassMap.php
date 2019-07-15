<?php

namespace Maestro\Loader;

use Maestro\Loader\Exception\UnknownAlias;

class AliasToClassMap
{
    private $map = [];

    public function __construct(array $map)
    {
        foreach ($map as $alias => $className) {
            $this->add($alias, $className);
        }
    }

    public function classNameFor(string $alias): string
    {
        if (!isset($this->map[$alias])) {
            throw new UnknownAlias(sprintf(
                'Alias "%s" is not known, known aliases: "%s"',
                $alias,
                implode('", "', array_keys($this->map))
            ));
        }

        return $this->map[$alias];
    }

    private function add($alias, $className)
    {
        $this->map[$alias] = $className;
    }
}
