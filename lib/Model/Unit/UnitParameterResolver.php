<?php

namespace Maestro\Model\Unit;

use Maestro\Model\ParameterResolverFactory;

class UnitParameterResolver
{
    /**
     * @var ParameterResolverFactory
     */
    private $factory;

    public function __construct(ParameterResolverFactory $factory)
    {
        $this->factory = $factory;
    }

    public function resolveParameters(Unit $unit, Parameters $parameters): Parameters
    {
        $resolver = $this->factory->create();

        $unit->configure($resolver);

        $parametersAsArray = $resolver->resolve($parameters->all());

        foreach ($resolver->getDefinedOptions() as $name) {
            if (!$parameters->has($name) && $parameters->hasGlobal($name)) {
                $parametersAsArray[$name] = $parameters->getGlobal($name);
            }
        }

        return $parameters->spawnLocal($parametersAsArray);
    }
}
