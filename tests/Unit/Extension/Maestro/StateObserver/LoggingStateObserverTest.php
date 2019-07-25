<?php

namespace Maestro\Tests\Unit\Extension\Maestro\StateObserver;

use Maestro\Graph\Node;
use Maestro\Graph\State;
use Maestro\Graph\StateChangeEvent;
use Maestro\Extension\Maestro\StateObserver\LoggingStateObserver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class LoggingStateObserverTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $logger;

    /**
     * @var LoggingStateObserver
     */
    private $observer;


    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->observer = new LoggingStateObserver($this->logger->reveal());
    }

    public function testLogsStateEvents()
    {
        $event = new StateChangeEvent(Node::create('foo'), State::BUSY(), State::CANCELLED());
        $this->observer->observe($event);
        $this->logger->info(Argument::containingString('foo'), [
            'env' => [],
            'vars' => [],
            'workspace' => null,
        ])->shouldHaveBeenCalled();
    }
}
