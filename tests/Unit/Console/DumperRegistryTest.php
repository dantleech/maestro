<?php

namespace Maestro\Tests\Unit\Console;

use Maestro\Console\Dumper;
use Maestro\Console\DumperRegistry;
use Maestro\Console\Exception\DumperNotFound;
use PHPUnit\Framework\TestCase;

class DumperRegistryTest extends TestCase
{
    public function testThrowsExceptionIfDumperNotFound()
    {
        $this->expectException(DumperNotFound::class);
        (new DumperRegistry())->get('asdasd');
    }

    public function testRetrievesDumper()
    {
        $dumper = $this->prophesize(Dumper::class);
        $this->assertSame(
            $dumper->reveal(),
            (new DumperRegistry(['foo' => $dumper->reveal()]))->get('foo')
        );
    }
}
