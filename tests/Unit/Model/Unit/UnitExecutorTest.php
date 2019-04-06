<?php

namespace Maestro\Tests\Unit\Model\Unit;

use Maestro\Model\Unit\Parameters;
use PHPUnit\Framework\TestCase;
use Maestro\Model\Unit\Exception\InvalidUnitConfiguration;
use Maestro\Model\ParameterResolver;
use Maestro\Model\ParameterResolverFactory;
use Maestro\Model\Unit\Unit;
use Maestro\Model\Unit\UnitExecutor;
use Maestro\Model\Unit\UnitRegistry;

class UnitExecutorTest extends TestCase
{
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
        $this->parameterResolverFactory = $this->prophesize(ParameterResolverFactory::class);
        $this->parameterResolver = $this->prophesize(ParameterResolver::class);
        $this->unit = $this->prophesize(Unit::class);

        $this->executor = new UnitExecutor(
            $this->parameterResolverFactory->reveal(),
            $this->registry->reveal()
        );

        $this->parameterResolverFactory->create()->willReturn($this->parameterResolver->reveal());
    }

    public function testThrowsExceptionIfUnitConfigurationDoesNotDefineAUnitType()
    {
        $this->expectException(InvalidUnitConfiguration::class);
        $this->executor->execute(Parameters::create(['foobar' => 'barfoo']));
    }

    public function testResolvesParametersAndExecutesUnit()
    {
        $this->registry->get('barfoo')->willReturn($this->unit->reveal());

        $this->parameterResolver->setRequired([UnitExecutor::PARAM_UNIT])->shouldBeCalled();
        $this->parameterResolver->resolve([
            UnitExecutor::PARAM_UNIT => 'barfoo',
        ])->willReturn([
            'hello' => 'world',
        ]);

        $this->executor->execute(Parameters::create([
            UnitExecutor::PARAM_UNIT => 'barfoo'
        ]));
        $this->unit->execute(Parameters::create([
            'hello' => 'world',
        ], [
            'unit' => 'barfoo',
        ]))->shouldBeCalled();
    }
}
