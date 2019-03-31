<?php

namespace Phpactor\Extension\Maestro\Unit;

use Phpactor\Extension\Maestro\Model\ParameterResolver;
use Phpactor\Extension\Maestro\Model\Unit;
use Phpactor\Extension\Maestro\Model\UnitExecutor;

class SequenceUnit implements Unit
{
    private $executor;

    public function __construct(UnitExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function configure(ParameterResolver $resolver): void
    {
        $resolver->setDefaults([
            'cwd' => getcwd(),
            'units' => [],
            'console' => 'main',
        ]);
        $resolver->setAllowedTypes('units', ['array']);
    }

    public function execute(array $params): void
    {
        foreach ($params['units'] as $unitConfig) {
            $unitConfig['cwd'] = $params['cwd'];
            $unitConfig['console'] = $params['console'];
            $this->executor->execute($unitConfig);
        }
    }
}
