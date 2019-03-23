<?php

namespace Phpactor\Extension\Maestro\Tests\Unit\Model\StateMachine;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Maestro\Model\StateMachine\Context;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\UnknownContextKey;

class ContextTest extends TestCase
{
    public function testItSetsAndGets()
    {
        $context = new Context();
        $context->set('foo', 'bar');

        $this->assertEquals('bar', $context->get('foo'));
    }

    public function testThrowsExceptionIfRequestedKeyDoesNotExist()
    {
        $this->expectException(UnknownContextKey::class);
        $context = new Context();
        $context->get('foo');
    }

    public function testItRemoves()
    {
        $context = new Context();
        $context->set('foo', 'bar');

        $this->assertTrue($context->has('foo'));

        $context->remove('foo');

        $this->assertFalse($context->has('foo'));
    }
}
