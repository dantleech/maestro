<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine\State;

use Phpactor\Extension\Maestro\Model\StateMachine\State;

class CallbackState implements State
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $execute;

    /**
     * @var callable
     */
    private $rollback;

    /**
     * @var callable
     */
    private $predicate;

    /**
     * @var array
     */
    private $dependsOn;

    public function __construct(
        string $name,
        callable $execute,
        callable $rollback,
        callable $predicate,
        array $dependsOn
    )
    {
        $this->name = $name;
        $this->execute = $execute;
        $this->rollback = $rollback;
        $this->predicate = $predicate;
        $this->dependsOn = $dependsOn;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function execute(): void
    {
        call_user_func($this->execute);
    }

    public function rollback(): void
    {
        call_user_func($this->rollback);
    }

    public function dependsOn(): array
    {
        return $this->dependsOn;
    }

    public function predicate(): bool
    {
        return call_user_func($this->predicate);
    }
}
