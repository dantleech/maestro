<?php

namespace Phpactor\Extension\Maestro\Module\System;

use Phpactor\Extension\Maestro\Model\StateMachine\Context;
use Phpactor\Extension\Maestro\Model\StateMachine\State;

class ConfigLoaded implements State
{
    const NAME = 'system.config_loaded';
    const VAR_PACKAGE_NAME = 'config.package_name';

    public function name(): string
    {
        return self::NAME;
    }

    public function execute(Context $context): void
    {
        $context->set(self::VAR_PACKAGE_NAME, 'hello/world');
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
        return [];
    }
}
