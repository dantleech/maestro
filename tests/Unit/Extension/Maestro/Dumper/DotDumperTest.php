<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Dumper;

use Maestro\Extension\Maestro\Dumper\DotDumper;

class DotDumperTest extends DumperTestCase
{
    public function testDump()
    {
        $dump = (new DotDumper())->dump($this->createGraph());
        $this->assertStringContainsString('digraph', $dump);
    }
}
