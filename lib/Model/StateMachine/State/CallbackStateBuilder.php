<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine\State;

use Phpactor\Extension\Maestro\Model\StateMachine\State;

final class CallbackStateBuilder
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

    public static function create(string $name): self
    {
        $new = new self();
        $new->name = $name;
        $new->execute = function () {};
        $new->rollback = function () {};
        $new->predicate = function () { return true; };
        $new->dependsOn = [];

        return $new;
    }

    public function onExecute(callable $onExecute): self
    {
        $this->execute = $onExecute;

        return $this;
    }

    public function onRollback(callable $rollback): self
    {
        $this->rollback = $rollback;

        return $this;
    }

    public function dependsOn(array $dependencies): self
    {
        $this->dependsOn = $dependencies;

        return $this;
    }

    public function satifisfiedIf(callable $predicate): self
    {
        $this->predicate = $predicate;

        return $this;
    }

    public function build(): CallbackState
    {
        return new CallbackState(
            $this->name,
            $this->execute,
            $this->rollback,
            $this->predicate,
            $this->dependsOn
        );
    }
}
