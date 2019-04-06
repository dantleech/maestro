<?php

namespace Maestro\Model\Unit;

interface UnitRegistry
{
    public function get(string $name): Unit;
}
