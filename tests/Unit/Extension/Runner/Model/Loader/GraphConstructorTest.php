<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model\Loader;

use Closure;
use Maestro\Extension\Runner\Model\Loader\GraphConstructor;
use Maestro\Extension\Runner\Model\Loader\Manifest;
use Maestro\Extension\Runner\Model\Loader\ManifestNode;
use Maestro\Extension\Runner\Model\Loader\PathExpander;
use Maestro\Extension\Runner\Task\InitTask;
use Maestro\Library\Graph\Graph;
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
        $manifest = ManifestNode::fromArray($manifest);
        $pathExpander = new PathExpander();
        $constructor = new GraphConstructor($pathExpander, $manifest);
        $graph = $constructor->construct();

        $assertion($graph);
    }

    public function provideBuildGraph()
    {
        yield 'empty' => [
            [
                'name' => 'root',
                'type' => NullTask::class,
            ],
            function (Graph $graph) {
                $this->assertCount(1, $graph->nodes());
                $this->assertInstanceOf(NullTask::class, $graph->roots()->get('/root')->task());
            }
        ];

        yield 'children nodes' => [
            [
                'name' => 'root',
                'type' => NullTask::class,
                'nodes' => [
                    'phpactor/phpactor' => [
                    ],
                ]
            ],
            function (Graph $graph) {
                $nodes = $graph->dependentsFor('/root');
                $this->assertCount(1, $nodes);
                $this->assertEquals('phpactor/phpactor', $nodes->get('/root/phpactor/phpactor')->label());
            }
        ];

        yield 'node with tags' => [
            [
                'name' => '',
                'type' => NullTask::class,
                'nodes' => [
                    'phpactor/phpactor' => [
                        'tags' => [ 'one' ],
                    ],
                ]
            ],
            function (Graph $graph) {
                $node = $graph->nodes()->get('/phpactor/phpactor');
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
