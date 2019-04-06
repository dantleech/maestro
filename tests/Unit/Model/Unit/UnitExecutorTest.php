<?php

namespace Maestro\Tests\Unit\Model\Unit;

use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\UnitParameterResolver;
use PHPUnit\Framework\TestCase;
use Maestro\Model\Unit\Exception\InvalidUnitConfiguration;
use Maestro\Model\ParameterResolver;
use Maestro\Model\ParameterResolverFactory;
use Maestro\Model\Unit\Unit;
use Maestro\Model\Unit\UnitExecutor;
use Maestro\Model\Unit\UnitRegistry;

class UnitExecutorTest extends TestCase
{
    const EXAMPLE_UNIT = 'barfoo';

    /**
     * @var ObjectProphecy|UnitRegistry
     */
    private $registry;

    /**
     * @var UnitExecutor
     */
    private $executor;

    /**
     * @var ObjectProphecy
     */
    private $parameterResolverFactory;

    /**
     * @var ObjectProphecy
     */
    private $parameterResolver;

    /**
     * @var ObjectProphecy
     */
    private $unit;

    protected function setUp(): void
    {
        $this->registry = $this->prophesize(UnitRegistry::class);
        $this->parameterResolver = $this->prophesize(UnitParameterResolver::class);
        $this->unit = $this->prophesize(Unit::class);

        $this->executor = new UnitExecutor(
            $this->parameterResolver->reveal(),
            $this->registry->reveal()
        );
    }

    public function testThrowsExceptionIfUnitConfigurationDoesNotDefineAUnitType()
    {
        $this->expectException(InvalidUnitConfiguration::class);
        $this->executor->execute(Parameters::create(['foobar' => self::EXAMPLE_UNIT]));
    }

    public function testResolvesParametersAndExecutesUnit()
    {
        $this->registry->get(self::EXAMPLE_UNIT)->willReturn($this->unit->reveal());

        $this->parameterResolver->resolveParameters($this->unit->reveal(), Parameters::create([
        ]))->willReturn(Parameters::create([
            'hello' => 'world',
        ]));

        $this->unit->execute(Parameters::create([
            'hello' => 'world'
        ]))->shouldBeCalled();

        $this->executor->execute(Parameters::create([
            UnitExecutor::PARAM_UNIT => self::EXAMPLE_UNIT
        ]));
    }
}
