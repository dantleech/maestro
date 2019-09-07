<?php

namespace Maestro\Extension\Runner\Loader;

use Maestro\Extension\Runner\Loader\Exception\UnknownAlias;

class AliasToClassMap
{
    private $map = [];

    /**
     * @var string
     */
    private $context;

    public function __construct(string $context, array $map)
    {
        foreach ($map as $alias => $className) {
            $this->add($alias, $className);
        }
        $this->context = $context;
    }

    public function classNameFor(string $alias): string
    {
        if (!isset($this->map[$alias])) {
            throw new UnknownAlias(sprintf(
                '%s "%s" is not known, known %s aliases: "%s"',
                ucfirst($this->context),
                $alias,
                $this->context,
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
