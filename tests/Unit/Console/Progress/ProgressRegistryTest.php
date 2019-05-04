<?php

namespace Maestro\Tests\Unit\Console\Progress;

use Maestro\Console\Progress\Exception\ProgressNotFound;
use Maestro\Console\Progress\Progress;
use Maestro\Console\Progress\ProgressRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ProgressRegistryTest extends TestCase
{
    /**
     * @var ObjectProphecy|Progress
     */
    private $progress1;

    protected function setUp(): void
    {
        $this->progress1 = $this->prophesize(Progress::class);
    }

    public function testThrowsExceptionIfProgressNotFound()
    {
        $this->expectException(ProgressNotFound::class);

        $this->create([
            'one' => $this->progress1->reveal()
        ])->get('foobar');
    }

    public function testReturnsProgress()
    {
        $progress = $this->create([
            'one' => $this->progress1->reveal()
        ])->get('one');

        $this->assertSame($this->progress1->reveal(), $progress);
    }

    private function create(array $map): ProgressRegistry
    {
        return new ProgressRegistry($map);
    }
}
