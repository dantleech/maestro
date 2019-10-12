<?php

namespace Maestro\Tests\Unit\Extension\Report\Model;

use Maestro\Extension\Task\Extension\TaskHandlerDefinition;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Artifact\Artifact;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Extension\Report\Model\MaestroGraphSerializer;
use Maestro\Library\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;

class MaestroGraphSerializerTest extends TestCase
{
    /**
     * @dataProvider provideSerializeGraph
     */
    public function testSerializeGraph(Graph $graph, array $expectedData)
    {
        $definitionMap = new TaskHandlerDefinitionMap([
            new TaskHandlerDefinition('foo', 'null', NullTask::class),
        ]);
        $this->assertEquals($expectedData, (
            new MaestroGraphSerializer($definitionMap)
        )->serialize($graph));
    }

    public function provideSerializeGraph()
    {
        yield 'empty' => [
            GraphBuilder::create()->build(),
            [
                'edges' => [
                ],
                'nodes' => [
                ],
            ]
        ];

        yield [
            GraphBuilder::create()
                ->addNode(Node::create('n1'))
                ->addNode(Node::create('n2'))
                ->addEdge(Edge::create('n1', 'n2'))
                ->build(),
            [
                'edges' => [
                    [
                        'from' => 'n1',
                        'to' => 'n2',
                    ],
                ],
                'nodes' => [
                    'n1' => $this->nodeRepresentation('n1', []),
                    'n2' => $this->nodeRepresentation('n2', []),
                ],
            ]
        ];

        yield 'serializes public properties of artifacts' => [
            GraphBuilder::create()
                ->addNode(Node::create('n1', [
                    'artifacts' => [
                        new TestArtifact(),
                    ]
                ]))
                ->build(),
            [
                'edges' => [
                ],
                'nodes' => [
                    'n1' => $this->nodeRepresentation('n1', [
                        'artifacts' => [
                            'Maestro-Tests-Unit-Extension-Report-Model-TestArtifact' => [
                                'pub' => 'bar',
                            ],
                        ],
                    ]),
                ],
            ]
        ];
    }

    private function nodeRepresentation(string $id, array $properties): array
    {
        $val = [
            'id' => $id,
            'label' => $id,
            'state' => 'idle',
            'exception' => '',
            'tags' => [],
            'task' => $this->taskRepresentation([]),
            'artifacts' => [],
        ];
        return array_merge($val, $properties);
    }

    private function taskRepresentation(array $data): array
    {
        return array_merge([
            'alias' => 'null',
            'description' => 'doing nothing',
            'class' => 'Maestro-Library-Task-Task-NullTask',
            'args' => [],
        ], $data);
    }
}

class TestArtifact implements Artifact
{
    public $pub = 'bar';
    private $pri = 'baz';
}
