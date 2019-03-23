<?php

namespace Phpactor\Extension\Maestro\Tests\Unit\Model\StateMachine;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\CircularReferenceDetected;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\PredicateNotSatisfied;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\StateNotFound;
use Phpactor\Extension\Maestro\Model\StateMachine\StateMachine;
use Phpactor\Extension\Maestro\Model\StateMachine\State\CallbackStateBuilder;

class StateMachineTest extends TestCase
{
    const EXAMPLE_STATE1 = 'foo';
    const EXAMPLE_STATE2 = 'state2';


    public function testTransitionsToGivenState()
    {
        $machine = $this->create([
            CallbackStateBuilder::create('bar')->build(),
        ])->goto('bar');

        $this->assertEquals('bar', $machine->state()->name());
    }

    public function testThrowsExceptionIfStateNotFound()
    {
        $this->expectException(StateNotFound::class);
        $machine = $this->create([
            CallbackStateBuilder::create('bar')->dependsOn([self::EXAMPLE_STATE1])->build(),
        ])->goto(self::EXAMPLE_STATE1);
    }

    public function testExecutesStateIfPredicateNotSatistfied()
    {
        $context = [];
        $state = CallbackStateBuilder::create(self::EXAMPLE_STATE1)
            ->satifisfiedIf(function () use (&$context) {
                return isset($context['fo']);
            })
            ->onExecute(function () use (&$context) {
                $context['fo'] = true;
            })
            ->build();

        $machine = $this->create([ $state ])->goto(self::EXAMPLE_STATE1);

        $this->assertArrayHasKey('fo', $context);
        $this->assertTrue($context['fo']);
    }

    public function testThrowsExceptionIfPredicateNotSatisfiedAfterExecute()
    {
        $this->expectException(PredicateNotSatisfied::class);

        $state = CallbackStateBuilder::create(self::EXAMPLE_STATE1)
            ->satifisfiedIf(function () use (&$context) {
                return isset($context['fo']);
            })
            ->build();

        $this->create([ $state ])->goto(self::EXAMPLE_STATE1);
    }

    public function testExecutesDependentStates()
    {
        $context = [];
        $state1 = CallbackStateBuilder::create(self::EXAMPLE_STATE1)
            ->satifisfiedIf(function () use (&$context) {
                return isset($context['one']);
            })
            ->onExecute(function () use (&$context) {
                $context['one'] = true;
            })
            ->build();

        $state2 = CallbackStateBuilder::create(self::EXAMPLE_STATE2)
            ->dependsOn([self::EXAMPLE_STATE1])
            ->build();

        $machine = $this->create([ $state1, $state2 ])->goto(self::EXAMPLE_STATE2);

        $this->assertArrayHasKey('one', $context, 'Dependent state executed');
        $this->assertTrue($context['one']);
    }

    public function testThrowsExceptionOnCircularDependency()
    {
        $this->expectException(CircularReferenceDetected::class);

        $context = [];
        $state1 = CallbackStateBuilder::create(self::EXAMPLE_STATE1)
            ->dependsOn([self::EXAMPLE_STATE2])
            ->build();

        $state2 = CallbackStateBuilder::create(self::EXAMPLE_STATE2)
            ->dependsOn([self::EXAMPLE_STATE1])
            ->build();

        $machine = $this->create([ $state1, $state2 ])->goto(self::EXAMPLE_STATE2);
    }

    private function create(array $states): StateMachine
    {
        return new StateMachine($states);
    }
}
