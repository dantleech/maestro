<?php

namespace Phpactor\Extension\Maestro\Model\UnitRegistry;

use Phpactor\Extension\Maestro\Model\Exception\UnitNotFound;
use Phpactor\Extension\Maestro\Model\Unit;
use Phpactor\Extension\Maestro\Model\UnitRegistry;

final class EagerUnitRegistry implements UnitRegistry
{
    /**
     * @var array
     */
    private $units = [];

    public function __construct(array $units)
    {
        foreach ($units as $name => $unit) {
            $this->add($name, $unit);
        }

        $this->units = $units;
    }

    public function get(string $name): Unit
    {
        if (!isset($this->units[$name])) {
            throw new UnitNotFound(sprintf(
                'Could not find unit with name "%s"', $name
            ));
        }
        return $this->units[$name];
    }

    private function add(string $name, Unit $unit): void
    {
        $this->units[$name] = $unit;
    }
}
