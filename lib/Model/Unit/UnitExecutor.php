<?php

namespace Phpactor\Extension\Maestro\Model\Unit;

use Phpactor\Extension\Maestro\Model\Exception\InvalidUnitConfiguration;
use Phpactor\Extension\Maestro\Model\ParameterResolver;
use Phpactor\Extension\Maestro\Model\ParameterResolverFactory;

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

    public function execute(array $config): void
    {
        $resolver = $this->factory->create();

        if (!isset($config[self::PARAM_UNIT])) {
            throw new InvalidUnitConfiguration(sprintf(
                'Each unit configuration must contain the "%s" key', self::PARAM_UNIT
            ));
        }

        $unit = $this->registry->get($config[self::PARAM_UNIT]);
        unset($config[self::PARAM_UNIT]);
        $unit->configure($resolver);
        $parameters = $resolver->resolve($config);
        $unit->execute($parameters);
    }
}
