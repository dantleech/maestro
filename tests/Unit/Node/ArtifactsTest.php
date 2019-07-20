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
            'vars' => [
                'foo' => 'bar'
            ],
        ]);
        $this->assertEquals('bar', $environment->get('foo'));
    }

    public function testHasMethodToDetermineIfArtifactExists()
    {
        $environment = Environment::create([
            'vars' => [
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
            'vars' => [
                'foo' => 'bar'
            ],
        ]);
        $environment->get('car');
    }

    public function testMergesEnvironment()
    {
        $environment1 = Environment::create([
            'vars' => [
                'foo' => 'bar'
            ],
        ]);
        $environment2 = Environment::create([
            'vars' => [
                'foo' => 'doo',
                'bar' => 'foo'
            ],
        ]);
        $expected = Environment::create([
            'vars' => [
                'foo' => 'doo',
                'bar' => 'foo'
            ],
        ]);

        $this->assertEquals($expected, $environment1->merge($environment2));
    }
}
