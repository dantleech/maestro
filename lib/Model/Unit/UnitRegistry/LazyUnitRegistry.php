<?php

namespace Phpactor\Extension\Maestro\Model\Unit\UnitRegistry;

use Closure;
use Exception;
use Phpactor\Extension\Maestro\Model\Exception\CouldNotLoadUnit;
use Phpactor\Extension\Maestro\Model\Exception\UnitNotFound;
use Phpactor\Extension\Maestro\Model\Unit\Unit;
use Phpactor\Extension\Maestro\Model\Unit\UnitRegistry;

class LazyUnitRegistry implements UnitRegistry
{
    /**
     * @var array
     */
    private $map = [];

    /**
     * @var Closure
     */
    private $loader;

    public function __construct(array $map, Closure $loader)
    {
        $this->map = $map;
        $this->loader = $loader;
    }

    public function get(string $name): Unit
    {
        if (!isset($this->map[$name])) {
            throw new UnitNotFound(sprintf(
                'Unit "%s" not found, known units: "%s"',
                $name, implode('", "', array_keys($this->map))
            ));
        }

        $id = $this->map[$name];

        $closure = $this->loader;

        try {
            $unit = $closure($id);
        } catch (Exception $exception) {
            throw new CouldNotLoadUnit(sprintf(
                'Unit "%s" with lazy ID "%s" could not be loaded', $name, $id
            ), 0, $exception);
        }

        if (!$unit instanceof Unit) {
            throw new CouldNotLoadUnit(sprintf(
                'Lazy unit loader for "%s" did not return an instance of "%s", it returned: %s',
                $name,
                Unit::class,
                is_object($unit) ? get_class($unit) : gettype($unit)
            ));
        }

        return $unit;
    }

}
