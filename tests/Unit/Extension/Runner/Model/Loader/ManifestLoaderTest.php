<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model\Loader;

use Maestro\Extension\Runner\Model\Loader\ManifestLoader;
use Maestro\Extension\Runner\Model\Loader\Manifest;
use Maestro\Extension\Runner\Model\Loader\ManifestNode;
use Maestro\Extension\Runner\Model\Loader\Processor;
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
            'nodes' => [
            ]
        ]);

        $this->assertInstanceOf(
            ManifestNode::class,
            $this->loadManifest('foo.json')
        );
    }

    public function testPreProcesses()
    {
        $this->createPlan('foo.json', [
            'nodes' => [
            ]
        ]);

        $processor = new class implements Processor {
            public function process(array $manifest): array
            {
                $manifest['nodes'] = [
                    'foobar/barfoo' => []
                ];

                return $manifest;
            }
        };

        $manifest = $this->loadManifest('foo.json', [
            $processor
        ]);
        $this->assertInstanceOf(
            ManifestNode::class,
            $manifest
        );

        $this->assertCount(1, $manifest->nodes());
        $this->assertEquals('foobar/barfoo', $manifest->nodes()[0]->name());
    }

    protected function loadManifest(string $path, array $processors = []): ManifestNode
    {
        return (new ManifestLoader($this->workspace()->path('/'), $path, $processors))->load($path);
    }
}
