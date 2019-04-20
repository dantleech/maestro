<?php

namespace Maestro\Model\Unit\Registry;

use Closure;
use Maestro\Model\Unit\Exception\UnitNotFound;
use Maestro\Model\Unit\Registry;
use Maestro\Model\Unit\Unit;
use RuntimeException;

class LazyCallbackRegistry implements Registry
{
    /**
     * @var array
     */
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function get(string $unitType): Unit
    {
        if (!isset($this->map[$unitType])) {
            throw new UnitNotFound(sprintf(
                'Could not find unit "%s", known units "%s"',
                $unitType, implode('", "', array_keys($this->map))
            ));
        }

        $callback = $this->map[$unitType];

        if (!$callback instanceof Closure) {
            throw new RuntimeException(sprintf(
                'Callback must be a closure, got "%s"',
                is_object($callback) ? get_class($callback) : gettype($callback)
            ));
        }

        $unit = $callback();

        if (!$unit instanceof Unit) {
            throw new RuntimeException(sprintf(
                'Object returned by closure factory must be an instanceof Unit, got "%s"',
                is_object($unit) ? get_class($unit) : gettype($unit)
            ));
        }

        return $unit;
    }
}
