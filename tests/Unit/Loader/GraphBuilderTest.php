<?php

namespace Maestro\Tests\Unit\Loader;

use Maestro\Loader\GraphBuilder;
use Maestro\Loader\Manifest;
use Maestro\Loader\TaskMap;
use Maestro\Task\Task\NullTask;
use Maestro\Task\Task\PackageTask;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class GraphBuilderTest extends TestCase
{
    public function testBuildGraph()
    {
        $builder = new GraphBuilder($this->taskMap());
        $manifest = Manifest::loadFromArray([
            'parameters' => [
                'one' => 'two',
            ],
            'packages' => [
                'my-vendor/my-package' => [
                    'tasks' => [
                        'one' => [
                            'type' => 'foobar',
                        ]
                    ],
                ],
            ],
        ]);

        $graph = $builder->build($manifest);

        $propertyAccessor = new PropertyAccessor(false, true);
        $this->assertEquals(
            PackageTask::class,
            get_class($propertyAccessor->getValue($graph, 'children[0].task')),
            'Package task is loaded',
        );

        $this->assertEquals(
            'my-vendor/my-package',
            $propertyAccessor->getValue($graph, 'children[0].task.name'),
            'Package name is correct',
        );

        $this->assertEquals(
            NullTask::class,
            get_class($propertyAccessor->getValue($graph, 'children[0].children[0].task')),
            'Package name',
        );
    }

    public function testPackagesCanInheritFromPrototypes()
    {
        $builder = new GraphBuilder($this->taskMap());
        $manifest = Manifest::loadFromArray([
            'parameters' => [
                'one' => 'two',
            ],
            'prototypes' => [
                'default' => [
                    'tasks' => [
                        'one' => [
                            'type' => 'foobar',
                        ]
                    ],
                ],
            ],
            'packages' => [
                'my-vendor/my-package' => [
                    'prototype' => 'default',
                ]
            ],
        ]);

        $graph = $builder->build($manifest);

        $propertyAccessor = new PropertyAccessor(false, true);

        $this->assertEquals(
            'my-vendor/my-package',
            $propertyAccessor->getValue($graph, 'children[0].task.name'),
            'Package name is correct',
        );

        $this->assertEquals(
            NullTask::class,
            get_class($propertyAccessor->getValue($graph, 'children[0].children[0].task')),
            'Package name',
        );
    }

    private function taskMap(): TaskMap
    {
        return new TaskMap([
            'package' => PackageTask::class,
            'foobar' => NullTask::class,
        ]);
    }
}
