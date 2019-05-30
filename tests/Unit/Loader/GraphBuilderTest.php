<?php

namespace Maestro\Tests\Unit\Loader;

use Closure;
use Maestro\Loader\Exception\GraphContainsCircularReference;
use Maestro\Loader\GraphBuilder;
use Maestro\Loader\Manifest;
use Maestro\Loader\TaskMap;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\Nodes;
use Maestro\Task\State;
use Maestro\Task\Task;
use Maestro\Task\Task\NullTask;
use Maestro\Extension\Maestro\Task\PackageTask;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
                    Node::create('root')
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
                $nodes = $graph->dependentsOf('root');
                $this->assertCount(1, $nodes);
                $this->assertEquals('phpactor/phpactor', $nodes->get(0)->name());
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
                $nodes = $graph->dependentsOf('root');
                $this->assertCount(1, $nodes);
                $this->assertEquals('phpactor/phpactor', $nodes->get(0)->name());
                $this->assertEquals('phpactor/phpactor', $nodes->get(0)->task()->name());
                $this->assertEquals(State::WAITING(), $nodes->get(0)->state());
                $tasks = $graph->dependentsOf('phpactor/phpactor');
                $this->assertEquals('task1', $tasks->get(0)->name());
                $this->assertEquals('task2', $tasks->get(1)->name());
                $this->assertEquals('foobar', $tasks->get(1)->task()->param1());
                $this->assertEquals('no', $tasks->get(1)->task()->param2());
            }
        ];

        yield 'merges tasks from prototype' => [
            [
                'prototypes' => [
                    'default' => [
                        'tasks' => [
                            'task1' => [
                                'type' => 'foobar',
                            ],
                        ],
                    ],
                ],
                'packages' => [
                    'foobar/barfoo' => [
                        'prototype' => 'default',
                    ],
                ]
            ],
            function (Graph $graph) {
                $nodes = $graph->dependentsOf('foobar/barfoo');
                $this->assertEquals('task1', $nodes->get(0)->name());
            },
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
