<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\HandlerRegistry\EagerHandlerRegistry;
use Maestro\Task\Node;
use Maestro\Task\State;
use Maestro\Task\TaskRunner;
use Maestro\Task\Task\NullHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class NodeTest extends TestCase
{
    public function testRootNodeReturnsNullForParent()
    {
        $rootNode = Node::createRoot();
        $this->assertNull($rootNode->parent());
    }

    public function testAddChild()
    {
        $rootNode = Node::createRoot();
        $node1 = new Node('one');
        $node2 = new Node('two');
        $node3 = new Node('three');

        $rootNode->addChild($node1);

        $this->assertEquals('one', $rootNode->child('one')->name());
        $this->assertNotSame($node1, $rootNode->child('one'));
    }

    public function testReturnsChildren()
    {
        $rootNode = Node::createRoot();
        $node1 = new Node('one');
        $node2 = new Node('two');
        $rootNode->addChild($node1);
        $rootNode->addChild($node2);

        $this->assertCount(2, $rootNode->children());
    }

    public function testThrowsExceptionIfChildNotFound()
    {
        $this->expectException(RuntimeException::class);

        $rootNode = Node::createRoot();
        $node1 = new Node('one');
        $node2 = new Node('two');
        $rootNode->addChild($node1);
        $rootNode->addChild($node2);

        $rootNode->child('foobar');
    }

    public function testReturnParent()
    {
        $rootNode = Node::createRoot();
        $child = $rootNode->addChild(new Node('one'));

        $this->assertSame($rootNode, $child->parent());
    }

    public function testDefaultStateIsWaiting()
    {
        $rootNode = Node::createRoot();
        $this->assertTrue($rootNode->state()->isWaiting());
    }

    public function testRunsTask()
    {
        $taskRunner = new TaskRunner(new EagerHandlerRegistry([
            new NullHandler()
        ]));
        $rootNode = Node::createRoot();
        $this->assertEquals(State::WAITING(), $rootNode->state());
        \Amp\Promise\wait($rootNode->run($taskRunner));
        $this->assertEquals(State::IDLE(), $rootNode->state());
    }
}
