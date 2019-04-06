<?php

namespace Phpactor\Extension\Maestro\Model;

use Phpactor\Extension\Maestro\Model\Unit;

interface UnitRegistry
{
    public function get(string $name): Unit;
}
