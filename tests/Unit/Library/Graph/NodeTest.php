<?php

namespace Maestro\Tests\Unit\Graph;

use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\NodeStateMachine;
use Maestro\Library\Graph\State;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class NodeTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $stateMachine;

    /**
     * @var ObjectProphecy
     */
    private $schedulerRegistry;


    protected function setUp(): void
    {
        $this->stateMachine = $this->prophesize(NodeStateMachine::class);
        $this->stateMachine->transition(Argument::type(Node::class), Argument::type(State::class))->will(function ($args) {
            return $args[1];
        });
    }

    public function testReturnsLabelIfGiven()
    {
        $rootNode = Node::create('root', [
            'label' => 'Foobar',
        ]);
        $this->assertEquals('Foobar', $rootNode->label());
    }

    public function testDefaultStateIsScheduled()
    {
        $rootNode = Node::create('root');
        $this->assertTrue($rootNode->state()->isScheduled());
    }
}
