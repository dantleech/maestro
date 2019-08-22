<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Dumper;

use Maestro\Extension\Maestro\Dumper\OverviewRenderer;

class OverviewRendererTest extends DumperTestCase
{
    public function testDumpsOverview()
    {
        $output = (new OverviewRenderer())->dump($this->createGraph());
        $this->assertStringContainsString('0 done, 2 hidden', $output);
    }
}
