<?php

namespace Maestro\Node\StateObserver;

use Maestro\Node\StateChangeEvent;
use Maestro\Node\StateObserver;
use Psr\Log\LoggerInterface;

class LoggingStateObserver implements StateObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var null|float
     */
    private $startTime = null;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function observe(StateChangeEvent $stateChangeEvent): void
    {
        if ($stateChangeEvent->from()->is($stateChangeEvent->to())) {
            return;
        }

        if (null === $this->startTime) {
            $this->startTime = microtime(true);
        }

        $this->logger->info(sprintf(
            "%-9s%-50s %7s => %s %s",
            number_format(microtime(true) - $this->startTime, 6),
            '['.$stateChangeEvent->node()->id().']',
            strtoupper($stateChangeEvent->from()->toString()),
            strtoupper($stateChangeEvent->to()->toString()),
            $stateChangeEvent->node()->task()->description()
        ), $stateChangeEvent->node()->environment()->toArray());
    }
}
