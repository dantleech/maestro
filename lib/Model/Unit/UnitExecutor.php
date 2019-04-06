<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Exception\InvalidUnitConfiguration;
use Maestro\Model\ParameterResolver;
use Maestro\Model\ParameterResolverFactory;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Unit;

class UnitExecutor
{
    const PARAM_UNIT = 'unit';

    /**
     * @var UnitRegistry
     */
    private $registry;

    /**
     * @var UnitParameterResolver
     */
    private $resolver;

    public function __construct(UnitParameterResolver $resolver, UnitRegistry $registry)
    {
        $this->registry = $registry;
        $this->resolver = $resolver;
    }

    public function execute(Parameters $parameters): void
    {
        if (!$parameters->has(self::PARAM_UNIT)) {
            throw new InvalidUnitConfiguration(sprintf(
                'Each unit configuration must contain the "%s" key', self::PARAM_UNIT
            ));
        }

        $unit = $this->registry->get($parameters->get(self::PARAM_UNIT));

        $parameters = $parameters->remove(self::PARAM_UNIT);

        $unit->execute($this->resolver->resolveParameters($unit, $parameters));
    }
}
