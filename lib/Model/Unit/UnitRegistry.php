<?php

namespace Phpactor\Extension\Maestro\Model\Unit;

use Phpactor\Extension\Maestro\Model\Unit\Unit;

interface UnitRegistry
{
    public function get(string $name): Unit;
}
