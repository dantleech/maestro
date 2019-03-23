<?php

namespace Phpactor\Extension\Maestro\Module\System;

use Phpactor\Extension\Maestro\Model\StateMachine\Context;
use Phpactor\Extension\Maestro\Model\StateMachine\State;
use Phpactor\Extension\Maestro\Module\System\ConfigLoaded;

class Initialized implements State
{
    const NAME = 'system.initialized';

    public function name(): string
    {
        return self::NAME;
    }

    public function execute(Context $context): void
    {
    }

    public function rollback(Context $context): void
    {
    }

    public function predicate(Context $context): bool
    {
        return true;
    }

    public function dependsOn(): array
    {
        return [
            ConfigLoaded::NAME
        ];
    }
}
