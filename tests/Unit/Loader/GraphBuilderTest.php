<?php

namespace Maestro\Tests\Unit\Loader;

use Maestro\Loader\GraphBuilder;
use Maestro\Loader\Manifest;
use Maestro\Loader\TaskMap;
use Maestro\Task\State;
use Maestro\Task\Task;
use Maestro\Task\Task\NullTask;
use Maestro\Task\Task\PackageTask;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class GraphBuilderTest extends TestCase
{
    /**
     * @dataProvider provideBuildGraph
     */
    public function testBuildGraph(array $manifest, array $expectedValues = [])
    {
        $builder = new GraphBuilder($this->taskMap());
        $manifest = Manifest::loadFromArray($manifest);
        $graph = $builder->build($manifest);

        $propertyAccessor = new PropertyAccessor(false, true);

        foreach ($expectedValues as $path => $expectedValue) {
            $this->assertEquals($expectedValue, $propertyAccessor->getValue($graph, $path));
        }
    }

    public function provideBuildGraph()
    {
        yield 'empty' => [
            [
            ],
            [
                'name' => 'root',
            ],
        ];

        yield 'package' => [
            [
                'packages' => [
                    'phpactor/phpactor' => [
                    ],
                ]
            ],
            [
                'name' => 'root',
                'children[0].name' => 'package',
                'children[0].task.name' => 'phpactor/phpactor',
                'children[0].state' => State::WAITING(),
            ],
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
            [
                'name' => 'root',
                'children[0].name' => 'package',
                'children[0].task.name' => 'phpactor/phpactor',
                'children[0].state' => State::WAITING(),
                'children[0].children[0].name' => 'task1',
                'children[0].children[1].task.param1' => 'foobar',
                'children[0].children[1].task.param2' => 'no',
            ],
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
            [
                'name' => 'root',
                'children[0].name' => 'package',
                'children[0].task.name' => 'foobar/barfoo',
                'children[0].state' => State::WAITING(),
                'children[0].children[0].name' => 'task1',
            ],
        ];

        yield 'builds task graph based on dependencies' => [
            [
                'packages' => [
                    'foobar/barfoo' => [
                        'tasks' => [
                            'one' => [
                                'type' => 'foobar',
                            ],
                            'two' => [
                                'type' => 'foobar',
                                'depends' => ['one'],
                            ],
                            'three' => [
                                'type' => 'foobar',
                                'depends' => ['two'],
                            ],
                            'four' => [
                                'type' => 'foobar',
                            ],
                        ],
                    ],
                ]
            ],
            [
                'name' => 'root',
                'children[0].task.name' => 'foobar/barfoo',
                'children[0].children[0].name' => 'one',
                'children[0].children[0].children[0].name' => 'two',
                'children[0].children[1].name' => 'four',
            ],
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
