<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine;

interface State
{
    public function name(): string;

    public function execute(): void;

    public function rollback(): void;

    public function predicate(): bool;

    public function dependsOn(): array;
}
