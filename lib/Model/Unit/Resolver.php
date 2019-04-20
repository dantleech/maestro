<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Config;
use Maestro\Model\Unit\Definition;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Unit;

interface Resolver
{
    public function resolveConfig(Unit $unit, array $rawConfig, Parameters $scope): array;
}
