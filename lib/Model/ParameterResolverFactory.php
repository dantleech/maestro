<?php

namespace Maestro\Model;

class ParameterResolverFactory
{
    public function create(): ParameterResolver
    {
        return new ParameterResolver();
    }
}
