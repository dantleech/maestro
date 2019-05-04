<?php

namespace Maestro\Tests\Unit\Console\Progress;

use Maestro\Console\Progress\SimpleProgress;
use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queues;
use PHPUnit\Framework\TestCase;

class SimpleProgressTest extends TestCase
{
    public function testRenderQueue()
    {
        $queues = Queues::create();
        $queues->get('foobar')->enqueue(new class implements Job {
        });
        $queues->get('foobar')->enqueue(new class implements Job {
        });
        $queues->get('barfoo')->enqueue(new class implements Job {
        });
        $queues->get('barfoo')->enqueue(new class implements Job {
        });

        $progress = new SimpleProgress();
        $rendered = $progress->render($queues);
        $this->assertEquals(<<<'EOT'
foobar XX
barfoo XX
EOT
        , $rendered);
    }
}
