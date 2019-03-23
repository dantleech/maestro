<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine\State;

use Phpactor\Extension\Maestro\Model\Logger;
use Phpactor\Extension\Maestro\Model\StateMachine\Context;
use Phpactor\Extension\Maestro\Model\StateMachine\State;

class LoggingState implements State
{
    /**
     * @var State
     */
    private $innerState;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(State $innerState, Logger $logger)
    {
        $this->innerState = $innerState;
        $this->logger = $logger;
    }

    public function name(): string
    {
        return $this->innerState->name();
    }

    public function execute(Context $context): void
    {
        $this->innerState->execute($context);
        $this->logger->write(' > ' . $this->name());
    }

    public function rollback(Context $context): void
    {
        $this->innerState->rollback($context);
    }

    public function predicate(Context $context): bool
    {
        $satisfied = $this->innerState->predicate($context);
        $this->logger->write(sprintf(' > predicate "%s" returned %s', $this->name(), $satisfied ? 'true' : 'false'));

        return $satisfied;
    }

    public function dependsOn(): array
    {
        return $this->innerState->dependsOn();
    }
}
