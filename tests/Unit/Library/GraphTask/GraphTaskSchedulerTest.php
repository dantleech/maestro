<?php

namespace Maestro\Tests\Unit\Library\GraphTask;

use Closure;
use Maestro\Library\GraphTask\GraphTaskScheduler;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\State;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\Task;
use Maestro\Tests\Unit\Library\Graph\NodeHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

class GraphTaskSchedulerTest extends TestCase
{
    /**
     * @dataProvider provideSchedule
     */
    public function testSchedule(Closure $builderCallback, Closure $assertionCallback)
    {
        $builder = GraphBuilder::create();
        $builderCallback($builder);
        $graph = $builder->build();

        $queue = new FifoQueue();
        $runner = new GraphTaskScheduler($queue);
        $runner->run($graph);
        $assertionCallback($graph, $queue);
    }

    public function provideSchedule()
    {
        yield 'single node with no task' => [
            function (GraphBuilder $builder) {
                $builder->addNode(Node::create('root'));
            },
            function (Graph $graph, Queue $queue) {
                $this->assertCount(1, $queue);
                $this->assertEquals(State::DISPATCHED(), $graph->nodes()->get('root')->state());
            }
        ];

        yield 'ignores child nodes while the root node is busy' => [
            function (GraphBuilder $builder) {
                $builder->addNode(
                    NodeHelper::setState(Node::create('root'), State::DISPATCHED())
                );
                $builder->addNode(Node::create('1'));
                $builder->addNode(Node::create('2'));
                $builder->addEdge(Edge::create('1', 'root'));
                $builder->addEdge(Edge::create('2', 'root'));
            },
            function (Graph $graph, Queue $queue) {
                $this->assertEquals(State::DISPATCHED(), $graph->nodes()->get('root')->state());
                $this->assertEquals(State::IDLE(), $graph->nodes()->get('1')->state());
                $this->assertEquals(State::IDLE(), $graph->nodes()->get('2')->state());
            }
        ];

        yield 'runs children when parent node is done' => [
            function (GraphBuilder $builder) {
                $builder->addNode(
                    NodeHelper::setState(Node::create('root'), State::DONE())
                );
                $builder->addNode(Node::create('1'));
                $builder->addNode(Node::create('2'));
                $builder->addEdge(Edge::create('1', 'root'));
                $builder->addEdge(Edge::create('2', 'root'));
            },
            function (Graph $graph, Queue $queue) {
                $this->assertEquals(State::DONE(), $graph->nodes()->get('root')->state());
                $this->assertEquals(State::DISPATCHED(), $graph->nodes()->get('1')->state());
                $this->assertEquals(State::DISPATCHED(), $graph->nodes()->get('2')->state());
                $this->assertCount(2, $queue);
            }
        ];

        yield 'artifacts from parent nodes are passed to jobs of child nodes' => [
            function (GraphBuilder $builder) {
                $artifact = new stdClass();
                $builder->addNode(
                    NodeHelper::setState(Node::create('root', [
                        'artifacts' => [
                            $artifact
                        ],
                    ]), State::DONE())
                );
                $builder->addNode(Node::create('1'));
                $builder->addNode(Node::create('2'));
                $builder->addEdge(Edge::create('1', 'root'));
                $builder->addEdge(Edge::create('2', 'root'));
            },
            function (Graph $graph, Queue $queue) {
                $this->assertCount(2, $queue);
                $job1 = $queue->dequeue();
                $this->assertCount(1, $job1->artifacts());
                $job2 = $queue->dequeue();
                $this->assertCount(1, $job2->artifacts());
            }
        ];
    }
}

class TestTask implements Task
{
    public function description(): string
    {
        return 'hello';
    }
}
