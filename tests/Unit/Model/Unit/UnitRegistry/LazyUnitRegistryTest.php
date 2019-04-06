<?php

namespace Maestro\Tests\Unit\Model\Unit\UnitRegistry;

use Closure;
use Exception;
use PHPUnit\Framework\TestCase;
use Maestro\Model\Unit\Exception\CouldNotLoadUnit;
use Maestro\Model\Unit\Exception\UnitNotFound;
use Maestro\Model\Unit\Unit;
use Maestro\Model\Unit\UnitRegistry\LazyUnitRegistry;

class LazyUnitRegistryTest extends TestCase
{
    /**
     * @var ObjectProphecy|Unit
     */
    private $unit;

    protected function setUp(): void
    {
        $this->unit = $this->prophesize(Unit::class);
    }

    public function testThrowsExceptionIfUnitNameNotRegistered()
    {
        $this->expectException(UnitNotFound::class);

        $registry = $this->create([
            'foo' => 'bar',
        ]);

        $registry->get('zed');
    }

    public function testThrowsExceptionIfLoaderThrowsException()
    {
        $this->expectException(CouldNotLoadUnit::class);
        $this->expectExceptionMessage('could not be loaded');

        $registry = $this->create([
            'foo' => 'bar',
        ], function () {
            throw new Exception('Foobar');
        });

        $registry->get('foo');
    }

    public function testThrowsExceptionIfLoaderDoesNotReturnAUnitInstance()
    {
        $this->expectException(CouldNotLoadUnit::class);
        $this->expectExceptionMessage('did not return an instance of ');
        $registry = $this->create([
            'foo' => 'bar',
        ], function () {
            return;
        });

        $registry->get('foo');
    }

    public function testPassesMappedNameToLoader()
    {
        $passedName = null;
        $registry = $this->create([
            'foo' => 'bar',
        ], function (string $name) use (&$passedName) {
            $passedName = $name;
            return $this->unit->reveal();
        });

        $registry->get('foo');
        self::assertEquals('bar', $passedName);
    }

    public function testLoadsUnit()
    {
        $registry = $this->create([
            'foo' => 'bar',
        ], function (string $name) {
            return $this->unit->reveal();
        });

        self::assertSame($this->unit->reveal(), $registry->get('foo'));
    }

    private function create(array $map, Closure $loader = null)
    {
        $loader = $loader ?: function () {};
        return new LazyUnitRegistry($map, $loader);
    }
}
