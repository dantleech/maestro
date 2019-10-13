<?php

namespace Maestro\Tests\Unit\Library\Graph;

use Closure;
use Maestro\Library\Graph\GraphTaskScheduler;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\State;
use Maestro\Library\Artifact\Artifact;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\Task;
use PHPUnit\Framework\TestCase;

class GraphTaskSchedulerTest extends TestCase
{
    /**
     * @dataProvider provideSchedule
     */
    public function testSchedule(Closure $builderCallback, Closure $assertionCallback)
    {
        $queue = new FifoQueue();
        $runner = new GraphTaskScheduler($queue);
        $builder = GraphBuilder::create();
        $builderCallback($builder, $runner);
        $graph = $builder->build();

        $runner->run($graph);
        $assertionCallback($graph, $queue, $runner);
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
                    NodeHelper::setState(Node::create('root'), State::SUCCEEDED())
                );
                $builder->addNode(Node::create('1'));
                $builder->addNode(Node::create('2'));
                $builder->addEdge(Edge::create('1', 'root'));
                $builder->addEdge(Edge::create('2', 'root'));
            },
            function (Graph $graph, Queue $queue) {
                $this->assertEquals(State::SUCCEEDED(), $graph->nodes()->get('root')->state());
                $this->assertEquals(State::DISPATCHED(), $graph->nodes()->get('1')->state());
                $this->assertEquals(State::DISPATCHED(), $graph->nodes()->get('2')->state());
                $this->assertCount(2, $queue);
            }
        ];

        yield 'artifacts from parent nodes are passed to jobs of child nodes' => [
            function (GraphBuilder $builder) {
                $artifact = new TestArtifact();
                $builder->addNode(
                    NodeHelper::setState(Node::create('root', [
                        'artifacts' => [
                            $artifact
                        ],
                    ]), State::SUCCEEDED())
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

        yield 'artifacts state does not leak on subsequent iterations' => [
            function (GraphBuilder $builder, GraphTaskScheduler $scheduler) {
                $artifact1 = new TestArtifact();
                $artifact1->id = 1;

                $builder->addNode(
                    NodeHelper::setState(Node::create('root'), State::SUCCEEDED())
                );
                $builder->addNode(Node::create('p1', [
                    'artifacts' => [
                        $artifact1
                    ]
                ]));
                $builder->addNode(Node::create('p2'));
                $builder->addNode(Node::create('p1.task1'));
                $builder->addNode(Node::create('p2.task1'));

                $builder->addEdge(Edge::create('p1', 'root'));
                $builder->addEdge(Edge::create('p2', 'root'));
                $builder->addEdge(Edge::create('p1.task1', 'p1'));
                $builder->addEdge(Edge::create('p2.task1', 'p2'));
            },
            function (Graph $graph, Queue $queue, GraphTaskScheduler $scheduler) {

                $this->assertCount(2, $queue, 'p1 and p2');
                $queue->dequeue();
                $queue->dequeue();

                NodeHelper::setState($graph->nodes()->get('p1'), State::SUCCEEDED());
                NodeHelper::setState($graph->nodes()->get('p2'), State::SUCCEEDED());

                $scheduler->run($graph);

                $this->assertCount(2, $queue, 'p1.task1 and p2.task1');
                $job1 = $queue->dequeue();
                $this->assertCount(1, $job1->artifacts(), 'p1.task1 inherits from package artifacts');
                $job2 = $queue->dequeue();
                $this->assertCount(0, $job2->artifacts(), 'p2.task1 inherits nothing from it\'s package');
            }
        ];

        yield 'cancels nodes depending on a failed node' => [
            function (GraphBuilder $builder) {
                $artifact = new TestArtifact();
                $builder->addNode(NodeHelper::setState(Node::create('root'), State::SUCCEEDED()));
                $builder->addNode(NodeHelper::setState(Node::create('n1'), State::FAILED()));
                $builder->addNode(NodeHelper::setState(Node::create('n2'), State::SUCCEEDED()));
                $builder->addNode(NodeHelper::setState(Node::create('n3'), State::IDLE()));
                $builder->addNode(NodeHelper::setState(Node::create('n4'), State::IDLE()));
                $builder->addNode(NodeHelper::setState(Node::create('n5'), State::IDLE()));
                $builder->addEdge(Edge::create('n3', 'n1'));
                $builder->addEdge(Edge::create('n4', 'n1'));
                $builder->addEdge(Edge::create('n5', 'n2'));
                $builder->addEdge(Edge::create('n1', 'root'));
                $builder->addEdge(Edge::create('n2', 'root'));
            },
            function (Graph $graph, Queue $queue) {
                $this->assertEquals(State::CANCELLED(), $graph->nodes()->get('n3')->state());
                $this->assertEquals(State::CANCELLED(), $graph->nodes()->get('n4')->state());
                $this->assertEquals(State::DISPATCHED(), $graph->nodes()->get('n5')->state());
            }
        ];
    }
}

class TestArtifact implements Artifact
{
    public $id;
    public function serialize(): array
    {
        return [];
    }
}
