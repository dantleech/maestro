<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\Node;
use Maestro\Task\Nodes;
use Maestro\Task\State;
use PHPUnit\Framework\TestCase;

class NodesTest extends TestCase
{
    public function testReturnsByStates()
    {
        $nodes = Nodes::fromNodes([
            Node::create('foo')
        ]);

        $this->assertCount(0, $nodes->byStates(State::BUSY()));
        $this->assertCount(1, $nodes->byStates(State::WAITING()));
        $this->assertCount(1, $nodes->byStates(State::BUSY(), State::WAITING()));
    }
}
