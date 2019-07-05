<?php

namespace Maestro\Tests\Unit\Node\StateObserver;

use Maestro\Node\Node;
use Maestro\Node\State;
use Maestro\Node\StateChangeEvent;
use Maestro\Node\StateObserver\LoggingStateObserver;
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
        $this->logger->debug(Argument::containingString('foo'), [])->shouldHaveBeenCalled();
    }
}
