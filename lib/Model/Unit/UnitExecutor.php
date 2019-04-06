<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Exception\InvalidUnitConfiguration;
use Maestro\Model\ParameterResolver;
use Maestro\Model\ParameterResolverFactory;

class UnitExecutor
{
    const PARAM_UNIT = 'unit';

    /**
     * @var UnitRegistry
     */
    private $registry;

    /**
     * @var ParameterResolverFactory
     */
    private $factory;

    public function __construct(ParameterResolverFactory $factory, UnitRegistry $registry)
    {
        $this->factory = $factory;
        $this->registry = $registry;
    }

    public function execute(Parameters $parameters): void
    {
        $resolver = $this->factory->create();

        if (!$parameters->has(self::PARAM_UNIT)) {
            throw new InvalidUnitConfiguration(sprintf(
                'Each unit configuration must contain the "%s" key', self::PARAM_UNIT
            ));
        }

        $unit = $this->registry->get($parameters->get(self::PARAM_UNIT));
        $resolver->setRequired([self::PARAM_UNIT]);

        $unit->configure($resolver);

        $parametersAsArray = $resolver->resolve($parameters->all());
        $unit->execute($parameters->spawnLocal($parametersAsArray));
    }
}
