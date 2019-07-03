<?php

namespace Maestro\Tests\Unit\Loader;

use Closure;
use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Loader\GraphBuilder;
use Maestro\Loader\Manifest;
use Maestro\Loader\TaskMap;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\Nodes;
use Maestro\Node\State;
use Maestro\Node\Task;
use Maestro\Node\Task\NullTask;
use Maestro\Extension\Maestro\Task\PackageTask;
use PHPUnit\Framework\TestCase;

class GraphBuilderTest extends TestCase
{
    /**
     * @dataProvider provideBuildGraph
     */
    public function testBuildGraph(array $manifest, Closure $assertion)
    {
        $builder = new GraphBuilder($this->taskMap());
        $manifest = Manifest::loadFromArray($manifest);
        $graph = $builder->build($manifest);

        $assertion($graph);
    }

    public function provideBuildGraph()
    {
        yield 'empty' => [
            [
            ],
            function (Graph $graph) {
                $this->assertEquals(Nodes::fromNodes([
                    Node::create('root', [
                        'task' => new ManifestTask(null),
                    ])
                ]), $graph->roots());
            }
        ];

        yield 'package' => [
            [
                'packages' => [
                    'phpactor/phpactor' => [
                    ],
                ]
            ],
            function (Graph $graph) {
                $nodes = $graph->dependentsFor('root');
                $this->assertCount(1, $nodes);
                $this->assertEquals('phpactor/phpactor', $nodes->get('phpactor/phpactor')->id());
            }
        ];

        yield 'package with tasks' => [
            [
                'packages' => [
                    'phpactor/phpactor' => [
                        'tasks' => [
                            'task1' => [
                                'type' => 'foobar',
                            ],
                            'task2' => [
                                'type' => 'example',
                                'parameters' => [
                                    'param1' => 'foobar',
                                ],
                            ]
                        ],
                    ],
                ]
            ],
            function (Graph $graph) {
                $nodes = $graph->dependentsFor('root');
                $this->assertCount(1, $nodes);
                $this->assertEquals('phpactor/phpactor', $nodes->get('phpactor/phpactor')->task()->name());
                $this->assertEquals(State::WAITING(), $nodes->get('phpactor/phpactor')->state());
                $tasks = $graph->dependentsFor('phpactor/phpactor');
                $this->assertEquals('foobar', $tasks->get('phpactor/phpactor/task2')->task()->param1());
                $this->assertEquals('no', $tasks->get('phpactor/phpactor/task2')->task()->param2());
            }
        ];
    }

    private function taskMap(): TaskMap
    {
        return new TaskMap([
            'package' => PackageTask::class,
            'foobar' => NullTask::class,
            'example' => ExampleTask::class,
        ]);
    }
}

class ExampleTask implements Task
{
    /**
     * @var string
     */
    private $param1;
    /**
     * @var string
     */
    private $param2;

    public function __construct(string $param1, string $param2 = 'no')
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }
    public function description(): string
    {
        return 'hallo';
    }

    public function param1(): string
    {
        return $this->param1;
    }

    public function param2(): string
    {
        return $this->param2;
    }
}
