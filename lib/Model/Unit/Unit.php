<?php

namespace Phpactor\Extension\Maestro\Model\Unit;

use Phpactor\Extension\Maestro\Model\ParameterResolver;

interface Unit 
{
    public function configure(ParameterResolver $resolver): void;

    public function execute(array $params): void;
}
