<?php

namespace Maestro\Model\Unit;

use Maestro\Model\ParameterResolver;

interface Unit
{
    public function configure(ParameterResolver $resolver): void;

    public function execute(Parameters $parameters): void;
}
