<?php

namespace Maestro\Tests\Unit\Library\GraphTaskRunner;

use Maestro\Library\GraphTaskRunner\ArtifactContainer;
use Maestro\Library\GraphTaskRunner\Exception\ArtifactNotFound;
use PHPUnit\Framework\TestCase;
use stdClass;

class ArtifactContainerTest extends TestCase
{
    public function testSetAndGetObject()
    {
        $object = new stdClass();
        $container = new ArtifactContainer();
        $container->set($object);
        $this->assertSame($object, $container->get(stdClass::class));
    }

    public function testCreateFromArrayOfObjects()
    {
        $object = new stdClass();
        $container = new ArtifactContainer([
            $object
        ]);
        $container->set($object);
        $this->assertSame($object, $container->get(stdClass::class));
    }

    public function testArtifactsCanBeOverwritten()
    {
        $object1 = new stdClass();
        $object2 = new stdClass();
        $container = new ArtifactContainer();
        $container->set($object1);
        $container->set($object2);
        $this->assertNotSame($object1, $container->get(stdClass::class));
        $this->assertSame($object2, $container->get(stdClass::class));
    }

    public function testThrowsExceptionWhenTryingToGetNonExistingArtifact()
    {
        $this->expectException(ArtifactNotFound::class);

        $container = new ArtifactContainer();
        $container->get(stdClass::class);
    }

    public function testSpawnsNewContainerMergingGivenArtifacts()
    {
        $object1 = new class extends stdClass {
        };
        $object2 = new class extends stdClass {
        };
        $object3 = new class extends stdClass {
        };

        $container1 = new ArtifactContainer([$object1]);
        $container2= $container1->spawnMutated([
            $object2,
            $object3
        ]);

        $this->assertNotSame($container1, $container2);
        $this->assertSame($object1, $container2->get(get_class($object1)));
    }
}
