<?php

namespace Maestro\Extension\CoreExtension\Unit;

use Maestro\Model\ParameterResolver;
use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Unit;
use Maestro\Model\Unit\UnitExecutor;

class RootUnit implements Unit
{
    const PARAM_PARAMETERS = 'parameters';
    const PARAM_UNITS = 'units';

    /**
     * @var UnitExecutor
     */
    private $unitExecutor;

    public function __construct(UnitExecutor $unitExecutor)
    {
        $this->unitExecutor = $unitExecutor;
    }

    public function configure(ParameterResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_PARAMETERS => [],
            self::PARAM_UNITS => []
        ]);
        $resolver->setAllowedTypes(self::PARAM_PARAMETERS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_UNITS, ['array']);
    }

    public function execute(Parameters $parameters): void
    {
        foreach ($parameters->get(self::PARAM_UNITS) as $unitParameters) {
            $unitParameters = $parameters->spawnLocal($unitParameters);

            $this->unitExecutor->execute($unitParameters);
        }
    }
}
