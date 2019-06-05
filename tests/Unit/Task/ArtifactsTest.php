<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\Artifacts;
use Maestro\Task\Exception\ArtifactNotFound;
use PHPUnit\Framework\TestCase;

class ArtifactsTest extends TestCase
{
    public function testReturnsArtifact()
    {
        $artifacts = Artifacts::create([
            'foo' => 'bar'
        ]);
        $this->assertEquals('bar', $artifacts->get('foo'));
    }

    public function testHasMethodToDetermineIfArtifactExists()
    {
        $artifacts = Artifacts::create([
            'foo' => 'bar'
        ]);
        $this->assertTrue($artifacts->has('foo'));
        $this->assertFalse($artifacts->has('bar'));
    }

    public function testThrowsExceptionUnknownArtifact()
    {
        $this->expectException(ArtifactNotFound::class);
        $artifacts = Artifacts::create([
            'foo' => 'bar'
        ]);
        $artifacts->get('car');
    }

    public function testMergesArtifacts()
    {
        $artifacts1 = Artifacts::create([
            'foo' => 'bar'
        ]);
        $artifacts2 = Artifacts::create([
            'foo' => 'doo',
            'bar' => 'foo'
        ]);
        $expected = Artifacts::create([
            'foo' => 'doo',
            'bar' => 'foo'
        ]);

        $this->assertEquals($expected, $artifacts1->merge($artifacts2));
    }
}
