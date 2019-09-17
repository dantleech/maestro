<?php

namespace Maestro\Tests\Unit\Extension\Loader;

use Closure;
use Maestro\Extension\Runner\Loader\GraphConstructor;
use Maestro\Extension\Runner\Loader\Manifest;
use Maestro\Extension\Runner\Task\InitTask;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\Nodes;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;

class GraphConstructorTest extends TestCase
{
    /**
     * @dataProvider provideBuildGraph
     */
    public function testBuildGraph(array $manifest, Closure $assertion)
    {
        $constructor = new GraphConstructor();
        $manifest = Manifest::loadFromArray($manifest);
        $graph = $constructor->construct($manifest);

        $assertion($graph);
    }

    public function provideBuildGraph()
    {
        yield 'empty' => [
            [
            ],
            function (Graph $graph) {
                $this->assertInstanceOf(InitTask::class, $graph->roots()->get('root')->task());
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

        yield 'package with tags' => [
            [
                'packages' => [
                    'phpactor/phpactor' => [
                        'tags' => [ 'one' ],
                    ],
                ]
            ],
            function (Graph $graph) {
                $package = $graph->nodes()->get('phpactor/phpactor');
                $this->assertContains('one', $package->tags());
            }
        ];

        yield 'task with tags' => [
            [
                'packages' => [
                    'phpactor/phpactor' => [
                        'tasks' => [
                            'task1' => [
                                'type' => NullTask::class,
                                'tags' => ['one'],
                            ],
                        ],
                    ],
                ]
            ],
            function (Graph $graph) {
                $node = $graph->nodes()->get('phpactor/phpactor/task1');
                $this->assertContains('one', $node->tags());
            }
        ];
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
