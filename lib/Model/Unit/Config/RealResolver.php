<?php

namespace Maestro\Model\Unit\Config;

use Maestro\Model\Unit\Config;
use Maestro\Model\Unit\Definition;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Resolver;
use Maestro\Model\Unit\Unit;

class RealResolver implements Resolver
{
    public function resolveConfig(Unit $unit, array $rawConfig, Parameters $scope): array
    {
        $configResolver = new Config();
        $unit->configure($configResolver);

        return $configResolver->resolve($rawConfig);
    }
}
