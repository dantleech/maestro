<?php

namespace Maestro\Tests\Unit\Library\Task;

use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Exception\ArtifactNotFound;
use PHPUnit\Framework\TestCase;
use stdClass;

class ArtifactsTest extends TestCase
{
    public function testSetAndGetObject()
    {
        $object = new stdClass();
        $container = new Artifacts();
        $container->set($object);
        $this->assertSame($object, $container->get(stdClass::class));
    }

    public function testCreateFromArrayOfObjects()
    {
        $object = new stdClass();
        $container = new Artifacts([
            $object
        ]);
        $container->set($object);
        $this->assertSame($object, $container->get(stdClass::class));
    }

    public function testArtifactsCanBeOverwritten()
    {
        $object1 = new stdClass();
        $object2 = new stdClass();
        $container = new Artifacts();
        $container->set($object1);
        $container->set($object2);
        $this->assertNotSame($object1, $container->get(stdClass::class));
        $this->assertSame($object2, $container->get(stdClass::class));
    }

    public function testThrowsExceptionWhenTryingToGetNonExistingArtifact()
    {
        $this->expectException(ArtifactNotFound::class);

        $container = new Artifacts();
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

        $container1 = new Artifacts([$object1]);
        $container2= $container1->spawnMutated([
            $object2,
            $object3
        ]);

        $this->assertNotSame($container1, $container2);
        $this->assertSame($object1, $container2->get(get_class($object1)));
    }
}
