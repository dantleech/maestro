<?php

namespace Maestro\Tests\Unit\Graph;

use Amp\Loop;
use Maestro\Library\GraphTask\Artifacts;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\State;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\TaskRunner;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
    }

    public function testReturnsLabelIfGiven()
    {
        $rootNode = Node::create('root', [
            'label' => 'Foobar',
        ]);
        $this->assertEquals('Foobar', $rootNode->label());
    }

    public function testUpdatesStateToFailedIfJobFails()
    {
        $task = $this->prophesize(Task::class);
        $taskRunner = $this->prophesize(TaskRunner::class);
        $taskRunner->run($task->reveal())->willThrow(new RuntimeException('Sorry'));
        $node = Node::create('root', [
            'label' => 'Foobar',
            'task' => $task->reveal(),
        ]);
        $queue = new FifoQueue();
        $artifacts = new Artifacts();

        $node->run($queue, $artifacts);

        $job = $queue->dequeue();
        $job->run($taskRunner->reveal());

        Loop::run();

        $this->assertEquals(State::FAILED(), $node->state());
    }
}
