<?php

namespace Maestro\Tests\Unit\Model\Package;

use Maestro\Model\Package\Exception\CircularReferenceDetected;
use Maestro\Model\Package\Exception\TargetNotFound;
use Maestro\Model\Package\Manifest;
use Maestro\Model\Package\ManifestItem;
use PHPUnit\Framework\TestCase;

class ManifestTest extends TestCase
{
    public function testReturnsAllItems()
    {
        $items = [
            new ManifestItem('one', 'foo', []),
            new ManifestItem('two', 'bar', []),
        ];
        $manifest = Manifest::fromItems($items);
        $this->assertEquals($items, iterator_to_array($manifest));
    }

    public function testCreatesManifestForTarget()
    {
        $items = [
            new ManifestItem('one', 'foo', []),
            new ManifestItem('two', 'bar', []),
        ];
        $manifest = Manifest::fromItems($items)->forTarget('one');
        $this->assertEquals(Manifest::fromItems([
            new ManifestItem('one', 'foo', []),
        ]), $manifest);
    }

    public function testReturnsUnmodifiedManifestIfTargetIsNull()
    {
        $items = [
            new ManifestItem('one', 'foo', []),
            new ManifestItem('two', 'bar', []),
        ];

        $originalManifest = Manifest::fromItems($items);
        $manifest = $originalManifest->forTarget(null);
        $this->assertSame($originalManifest, $manifest);
    }

    public function testThrowsExceptionIfTargetNotFound()
    {
        $this->expectException(TargetNotFound::class);
        $originalManifest = Manifest::fromItems([
            new ManifestItem('one', 'foo', []),
        ]);
        $originalManifest->forTarget('two');
    }

    public function testReturnsDependenciesOfTarget()
    {
        $manifest = Manifest::fromArray([
            'one' => [
                'type' => 'foo',
                'depends' => ['two'],
            ],
            'two' => [
                'type' => 'foo',
                'depends' => ['three']
            ],
            'three' => [
                'type' => 'foo',
            ],
            'four' => [
                'type' => 'foo',
            ],
        ]);
        $this->assertEquals(Manifest::fromArray([
            'one' => [
                'type' => 'foo',
                'depends' => ['two'],
            ],
            'two' => [
                'type' => 'foo',
                'depends' => ['three']
            ],
            'three' => [
                'type' => 'foo',
            ],
        ]), $manifest->forTarget('one'));
    }

    public function testThrowsExceptionOnCircularReference()
    {
        $this->expectException(CircularReferenceDetected::class);
        Manifest::fromArray([
            'one' => [
                'type' => 'foo',
                'depends' => ['two'],
            ],
            'two' => [
                'type' => 'foo',
                'depends' => ['one']
            ],
        ])->forTarget('one');
    }
}
