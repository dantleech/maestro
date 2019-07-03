<?php

namespace Maestro\Tests\Unit\Node;

use Maestro\Node\Edge;
use Maestro\Node\Edges;
use PHPUnit\Framework\TestCase;

class EdgesTest extends TestCase
{
    public function testRemovesReferencesToNode()
    {
        $edges = Edges::fromEdges([
            Edge::create('n1', 'n2'),
            Edge::create('n2', 'n3'),
            Edge::create('n4', 'n1'),
        ]);
        $expectedEdges = Edges::fromEdges([
            Edge::create('n1', 'n2'),
            Edge::create('n4', 'n1'),
        ]);
        $this->assertEquals(
            $expectedEdges,
            $edges->removeReferencesTo('n3')
        );
    }
}
