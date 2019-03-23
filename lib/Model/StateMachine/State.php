<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine;

interface State
{
    public function name(): string;

    public function execute(Context $context): void;

    public function rollback(Context $context): void;

    public function predicate(Context $context): bool;

    public function dependsOn(): array;
}
