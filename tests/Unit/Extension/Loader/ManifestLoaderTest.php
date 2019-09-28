<?php

namespace Maestro\Tests\Unit\Loader;

use Maestro\Extension\Runner\Loader\ManifestLoader;
use Maestro\Extension\Runner\Loader\Manifest;
use Maestro\Extension\Runner\Loader\Processor;
use Maestro\Tests\IntegrationTestCase;

class ManifestLoaderTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testLoadsManifest()
    {
        $this->createPlan('foo.json', [
            'packages' => [
            ]
        ]);

        $this->assertInstanceOf(
            Manifest::class,
            $this->loadManifest('foo.json')
        );
    }

    public function testPreProcesses()
    {
        $this->createPlan('foo.json', [
            'packages' => [
            ]
        ]);

        $processor = new class implements Processor {
            public function process(array $manifest): array
            {
                $manifest['packages'] = [
                    'foobar/barfoo' => []
                ];

                return $manifest;
            }
        };

        $manifest = $this->loadManifest('foo.json', [
            $processor
        ]);
        $this->assertInstanceOf(
            Manifest::class,
            $manifest
        );

        $this->assertCount(1, $manifest->packages());
        $this->assertEquals('foobar/barfoo', $manifest->packages()[0]->name());
    }

    protected function loadManifest(string $path, array $processors = []): Manifest
    {
        return (new ManifestLoader($this->workspace()->path('/'), $path, $processors))->load($path);
    }
}
