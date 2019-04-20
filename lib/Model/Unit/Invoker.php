<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Config;
use Maestro\Model\Unit\Invoker;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Registry;
use Maestro\Model\Unit\Config\Resolver;

class Invoker
{
    /**
     * @var Registry
     */
    private $unitRegistry;

    /**
     * @var Resolver
     */
    private $resolver;

    public function __construct(Registry $unitRegistry, Resolver $resolver)
    {
        $this->unitRegistry = $unitRegistry;
        $this->resolver = $resolver;
    }

    public function invoke(Definition $definition, Parameters $parameters): void
    {
        $unit = $this->unitRegistry->get($definition->type());
        $config = $this->resolver->resolveConfig($unit, $definition->config(), $parameters);

        $unit->execute($config, $parameters->copy());
    }
}
