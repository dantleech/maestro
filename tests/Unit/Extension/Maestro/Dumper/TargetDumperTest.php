<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Dumper;

use Maestro\Extension\Maestro\Dumper\TargetDumper;

class TargetDumperTest extends DumperTestCase
{
    public function testDumpsTargets()
    {
        $output = (new TargetDumper())->dump($this->createGraph());
        $this->assertStringContainsString('n1', $output);
        $this->assertStringContainsString('n2', $output);
        $this->assertStringContainsString('n3', $output);
    }
}
