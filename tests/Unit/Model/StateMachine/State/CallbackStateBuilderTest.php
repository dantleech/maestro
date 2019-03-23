<?php

namespace Phpactor\Extension\Maestro\Tests\Unit\Model\StateMachine\State;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Maestro\Model\StateMachine\State\CallbackStateBuilder;

class CallbackStateBuilderTest extends TestCase
{
    public function testBuildsCallbackState()
    {
        $context = [
            'executed' => false,
            'rolledBack' => false,
            'satisfied' => false,
        ];
        $state = CallbackStateBuilder::create('foobar')
            ->onExecute(function () use (&$context) { $context['executed'] = true; })
            ->onRollback(function () use (&$context) { $context['rolledBack'] = true; })
            ->dependsOn(['foobar'])
            ->satifisfiedIf(function () use (&$context) { $context['satisfied'] = true; })
            ->build();

        $this->assertEquals(['foobar'], $state->dependsOn());
    }

    public function testBuildsStateWithNameOnly()
    {
        $state = CallbackStateBuilder::create('foo')->build();
        $this->assertEquals('foo', $state->name());
    }
}
