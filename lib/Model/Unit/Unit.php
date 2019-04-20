<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Config;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Config\Resolver;

interface Unit
{
    public function configure(Config $config);

    public function execute(array $config, Parameters $parameters);
}
