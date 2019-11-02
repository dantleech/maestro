<?php

namespace Maestro\Tests\Unit\Graph;

use Amp\Loop;
use Amp\Success;
use Maestro\Library\Artifact\Artifact;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\State;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\TaskRunner\InvokingTaskRunner;
use PHPUnit\Framework\TestCase;

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
        $taskRunner = $this->prophesize(InvokingTaskRunner::class);
        $exception = new TaskFailure('Sorry');
        $taskRunner->run($task->reveal(), new Artifacts([]))->willThrow($exception);
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
        $this->assertSame($exception, $node->exception());
    }

    public function testDefaultArtifactsAreGraftedOntoGivenArtifacts()
    {
        $task = $this->prophesize(Task::class);
        $artifact1 = $this->prophesize(Artifact::class);
        $artifact2 = $this->prophesize(Artifact::class);

        $taskRunner = $this->prophesize(InvokingTaskRunner::class);

        $taskRunner->run($task->reveal(), new Artifacts([
            $artifact2->reveal(),
            $artifact1->reveal(),
        ]))->willReturn(new Success([]))->shouldBeCalled();

        $node = Node::create('root', [
            'label' => 'Foobar',
            'task' => $task->reveal(),
            'artifacts' => [
                $artifact1->reveal(),
            ],
        ]);

        $queue = new FifoQueue();
        $artifacts = new Artifacts([$artifact2->reveal()]);

        $node->run($queue, $artifacts);

        $job = $queue->dequeue();
        $job->run($taskRunner->reveal());

        Loop::run();

        $this->assertEquals(State::SUCCEEDED(), $node->state());
        self::assertCount(1, $node->artifacts());
    }
}
