<?php

namespace Maestro\Model\Unit;

interface Registry
{
    public function get(string $unit): Unit;
}
