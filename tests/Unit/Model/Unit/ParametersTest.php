<?php

namespace Maestro\Tests\Unit\Model\Unit;

use Maestro\Model\Unit\Exception\ParameterNotFound;
use Maestro\Model\Unit\Parameters;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
    public function testThrowsExceptionIfParameterNotFound()
    {
        $this->expectException(ParameterNotFound::class);
        Parameters::create([
            'foo' => 'bar',
        ])->get('bar');
    }

    public function testReturnsLocalParameters()
    {
        self::assertEquals('bar', Parameters::create([
            'foo' => 'bar',
        ])->get('foo'));
    }

    public function testSpawnsNewLocalParametersAndPushesOldLocalToGlobal()
    {
        $parameters = Parameters::create(['foo' => 'bar']);
        $parameters = $parameters->spawnLocal(['bar' => 'foo']);

        $this->assertEquals(Parameters::create(
            ['bar' => 'foo'],
            ['foo' => 'bar']
        ), $parameters);
    }
}
