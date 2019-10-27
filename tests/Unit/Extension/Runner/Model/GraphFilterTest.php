<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model;

use Closure;
use Maestro\Extension\Runner\Model\GraphFilter;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Tests\IntegrationTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class GraphFilterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideFilter
     */
    public function testFilter(Closure $builderCallback, string $filter, array $expectedIds)
    {
        $builder = GraphBuilder::create();
        $builderCallback($builder);

        $this->assertEquals(
            $expectedIds,
            (new GraphFilter($this->container()->get(SerializerInterface::class)))->filter($builder->build(), $filter)->nodes()->ids()
        );
    }

    public function provideFilter()
    {
        yield 'empty returns unmodified graph' => [
            function (GraphBuilder $builder) {
                $builder->addNode(Node::create('n1'));
            },
            '',
            ['n1']
        ];

        yield 'filter by id' => [
            function (GraphBuilder $builder) {
                $builder->addNode(Node::create('n1'));
            },
            'id == "n1"',
            ['n1']
        ];

        yield 'execute only a certain branch' => [
            function (GraphBuilder $builder) {
                $builder->addNode(Node::create('/foobar/n1'));
            },
            'branch("/foobar")',
            ['/foobar/n1']
        ];
    }
}
