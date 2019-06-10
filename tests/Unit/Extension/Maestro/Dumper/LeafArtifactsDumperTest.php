<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Dumper;

use Maestro\Extension\Maestro\Dumper\LeafArtifactsDumper;

class LeafArtifactsDumperTest extends DumperTestCase
{
    public function testDumpsArtifacts()
    {
        $output = (new LeafArtifactsDumper())->dump($this->createGraph());
        $this->assertStringContainsString('n3', $output);
    }
}
