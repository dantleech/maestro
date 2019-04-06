<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Unit;

interface UnitRegistry
{
    public function get(string $name): Unit;
}
