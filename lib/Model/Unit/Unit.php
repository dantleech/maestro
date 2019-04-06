<?php

namespace Phpactor\Extension\Maestro\Model;

interface Unit 
{
    public function configure(ParameterResolver $resolver): void;

    public function execute(array $params): void;
}
