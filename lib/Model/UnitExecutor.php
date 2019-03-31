<?php

namespace Phpactor\Extension\Maestro\Model;

use Phpactor\Extension\Maestro\Model\Exception\InvalidUnitConfiguration;

class UnitExecutor
{
    const PARAM_UNIT = 'unit';

    /**
     * @var UnitRegistry
     */
    private $registry;

    public function __construct(UnitRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function execute(array $config): void
    {
        $resolver = new ParameterResolver();

        if (!isset($config[self::PARAM_UNIT])) {
            throw new InvalidUnitConfiguration(
                'Each unit configuration must contain the "type" key'
            );
        }

        $unit = $this->registry->get($config[self::PARAM_UNIT]);
        unset($config[self::PARAM_UNIT]);
        $unit->configure($resolver);
        $parameters = $resolver->resolve($config);
        $unit->execute($parameters);
    }
}
