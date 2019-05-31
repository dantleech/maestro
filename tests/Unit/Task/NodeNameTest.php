<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\NodeName;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class NodeNameTest extends TestCase
{
    public function testTestFromUnknown()
    {
        $name = NodeName::fromUnknown('foo');
        $this->assertInstanceOf(NodeName::class, $name);
        $this->assertEquals('foo', $name->toString());

        $name = NodeName::fromUnknown(NodeName::fromUnknown('foobar'));
        $this->assertInstanceOf(NodeName::class, $name);
        $this->assertEquals('foobar', $name->toString());
    }

    public function testExceptionOnUnknown()
    {
        $this->expectException(RuntimeException::class);
        $name = NodeName::fromUnknown(new stdClass());
    }

    public function testReturnsNamespace()
    {
        $name = NodeName::fromParts(['foobar', 'barfoo']);
        $this->assertEquals('foobar', $name->namespace()->toString());
        $this->assertEquals('foobar/barfoo', $name->toString());
    }

    public function testReturnsNullIfNoNamespace()
    {
        $name = NodeName::fromParts(['barfoo']);
        $this->assertNull($name->namespace());
    }

    public function testReturnsShortName()
    {
        $name = NodeName::fromParts(['foobar', 'barfoo']);
        $this->assertEquals('barfoo', $name->shortName());
    }

    public function testExceptionIfNameHasZeroParts()
    {
        $this->expectException(RuntimeException::class);
        NodeName::fromParts([]);
    }
}
