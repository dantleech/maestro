<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Dumper;

use Maestro\Extension\Maestro\Dumper\OverviewRenderer;

class OverviewRendererTest extends DumperTestCase
{
    public function testDumpsOverview()
    {
        $output = (new OverviewRenderer())->dump($this->createGraph());
        $this->assertStringContainsString('n2', $output);
        $this->assertStringContainsString('n3', $output);
    }
}
