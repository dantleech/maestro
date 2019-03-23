<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine\Machine;

use Phpactor\Extension\Maestro\Model\Logger;
use Phpactor\Extension\Maestro\Model\StateMachine\State;
use Phpactor\Extension\Maestro\Model\StateMachine\StateMachine;

class LoggingStateMachine implements StateMachine
{
    /**
     * @var StateMachine
     */
    private $innerStateMachine;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(StateMachine $innerStateMachine, Logger $logger)
    {
        $this->innerStateMachine = $innerStateMachine;
        $this->logger = $logger;
    }

    public function goto(string $name): StateMachine
    {
        $this->logger->write('Applying: ' . $name);
        $this->innerStateMachine->goto($name);
        return $this;

    }

    public function state(): State
    {
        return $this->innerStateMachine->state();
    }
}
