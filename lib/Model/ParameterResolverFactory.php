<?php

namespace Phpactor\Extension\Maestro\Model;

class ParameterResolverFactory
{
    public function create(): ParameterResolver
    {
        return new ParameterResolver();
    }
}
