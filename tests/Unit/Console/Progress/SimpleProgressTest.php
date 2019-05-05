<?php

namespace Maestro\Tests\Unit\Console\Progress;

use Maestro\Console\Progress\SimpleProgress;
use Maestro\Model\Job\QueueMonitor;
use Maestro\Model\Job\QueueStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;

class SimpleProgressTest extends TestCase
{
    public function testRenderQueue()
    {
        $monitor = new QueueMonitor([
            new QueueStatus('foobar', 2),
            new QueueStatus('barfoo', 2),
        ]);
        $progress = new SimpleProgress($monitor);
        $rendered = $progress->render();
        $this->assertStringContainsString(<<<'EOT'
  [  ] foobar                                        0/2 ()
  [  ] barfoo                                        0/2 ()
EOT
        , Helper::removeDecoration(new OutputFormatter(), $rendered));
    }
}
