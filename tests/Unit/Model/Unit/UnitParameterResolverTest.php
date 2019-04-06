<?php

namespace Maestro\Tests\Unit\Model\Unit;

use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\UnitParameterResolver;
use PHPUnit\Framework\TestCase;
use Maestro\Model\ParameterResolver;
use Maestro\Model\ParameterResolverFactory;
use Maestro\Model\Unit\Unit;

class UnitParameterResolverTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $parameterResolverFactory;

    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @var ObjectProphecy
     */
    private $unit;

    protected function setUp(): void
    {
        $this->parameterResolverFactory = $this->prophesize(ParameterResolverFactory::class);
        $this->parameterResolver = new ParameterResolver();
        $this->unit = $this->prophesize(Unit::class);

        $this->resolver = new UnitParameterResolver(
            $this->parameterResolverFactory->reveal()
        );

        $this->parameterResolverFactory->create()->willReturn($this->parameterResolver);
    }

    public function testResolvesParameters()
    {
        $this->parameterResolver->setDefaults([
            'foo' => 'bar',
            'hello' => 'world'
        ]);

        $parameters = $this->resolver->resolveParameters($this->unit->reveal(), Parameters::create([
            'foo' => 'bar',
        ]));

        self::assertEquals(
            Parameters::create([
                'foo' => 'bar',
                'hello' => 'world'
            ], ['foo' => 'bar']),
            $parameters
        );
    }

    public function testInjectsGlobalParameters()
    {
        $this->parameterResolver->setDefaults([
            'foo' => 'bar',
            'global1' => null
        ]);

        $parameters = $this->resolver->resolveParameters(
            $this->unit->reveal(),
            Parameters::create([
                'foo' => 'bar',
            ], ['global1' => 'hai!'])
        );

        self::assertEquals(
            Parameters::create([
                'foo' => 'bar',
                'global1' => 'hai!'
            ], [
                'foo' => 'bar',
                'global1' => 'hai!'
            ]),
            $parameters
        );
    }

    public function testLocalsOverrideGlobals()
    {
        $this->parameterResolver->setDefaults([
            'foo' => 'bar',
            'global1' => null
        ]);

        $parameters = $this->resolver->resolveParameters(
            $this->unit->reveal(),
            Parameters::create([
                'foo' => 'bar',
                'global1' => 'noo',
            ], ['global1' => 'hai!'])
        );

        self::assertEquals(
            Parameters::create([
                'foo' => 'bar',
                'global1' => 'noo'
            ], [
                'foo' => 'bar',
                'global1' => 'hai!'
            ]),
            $parameters
        );
    }
}
