<?php

namespace Maestro\Extension\CoreExtension\Unit;

use Maestro\Model\Unit\Config;
use Maestro\Model\Unit\Definition;
use Maestro\Model\Unit\Environment;
use Maestro\Model\Unit\Invoker;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Unit;

class Root implements Unit
{
    /**
     * @var Invoker
     */
    private $invoker;

    public function __construct(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }

    public function configure(Config $config)
    {
        $config->setDefaults([
            'parameters' => [],
            'units' => [],
        ]);
    }

    public function execute(Environment $environment, array $config)
    {
        $environment->parameters()->mergeArray($config['parameters']);

        foreach ($config['units'] as $unit) {
            $definition = Definition::fromArray($unit);
            $this->invoker->invoke($environment, $definition);
        }
    }
}
