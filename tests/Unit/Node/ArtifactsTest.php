<?php

namespace Maestro\Tests\Unit\Node;

use Maestro\Node\Environment;
use Maestro\Node\Exception\ArtifactNotFound;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public function testReturnsArtifact()
    {
        $environment = Environment::create([
            'foo' => 'bar'
        ]);
        $this->assertEquals('bar', $environment->get('foo'));
    }

    public function testHasMethodToDetermineIfArtifactExists()
    {
        $environment = Environment::create([
            'foo' => 'bar'
        ]);
        $this->assertTrue($environment->has('foo'));
        $this->assertFalse($environment->has('bar'));
    }

    public function testThrowsExceptionUnknownArtifact()
    {
        $this->expectException(ArtifactNotFound::class);
        $environment = Environment::create([
            'foo' => 'bar'
        ]);
        $environment->get('car');
    }

    public function testMergesEnvironment()
    {
        $environment1 = Environment::create([
            'foo' => 'bar'
        ]);
        $environment2 = Environment::create([
            'foo' => 'doo',
            'bar' => 'foo'
        ]);
        $expected = Environment::create([
            'foo' => 'doo',
            'bar' => 'foo'
        ]);

        $this->assertEquals($expected, $environment1->merge($environment2));
    }
}
