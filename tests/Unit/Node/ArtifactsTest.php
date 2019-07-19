<?php

namespace Maestro\Tests\Unit\Node;

use Maestro\Node\Environment;
use Maestro\Node\Exception\ParameterNotFound;
use PHPUnit\Framework\TestCase;

class ArtifactsTest extends TestCase
{
    public function testReturnsArtifact()
    {
        $environment = Environment::create([
            'parameters' => [
                'foo' => 'bar'
            ],
        ]);
        $this->assertEquals('bar', $environment->get('foo'));
    }

    public function testHasMethodToDetermineIfArtifactExists()
    {
        $environment = Environment::create([
            'parameters' => [
                'foo' => 'bar'
            ],
        ]);
        $this->assertTrue($environment->has('foo'));
        $this->assertFalse($environment->has('bar'));
    }

    public function testThrowsExceptionUnknownArtifact()
    {
        $this->expectException(ParameterNotFound::class);
        $environment = Environment::create([
            'parameters' => [
                'foo' => 'bar'
            ],
        ]);
        $environment->get('car');
    }

    public function testMergesEnvironment()
    {
        $environment1 = Environment::create([
            'parameters' => [
                'foo' => 'bar'
            ],
        ]);
        $environment2 = Environment::create([
            'parameters' => [
                'foo' => 'doo',
                'bar' => 'foo'
            ],
        ]);
        $expected = Environment::create([
            'parameters' => [
                'foo' => 'doo',
                'bar' => 'foo'
            ],
        ]);

        $this->assertEquals($expected, $environment1->merge($environment2));
    }
}
