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
        $artifacts = new Artifacts();
        $artifacts->set($object);
        $this->assertSame($object, $artifacts->get(stdClass::class));
    }

    public function testCreateFromArrayOfObjects()
    {
        $object = new stdClass();
        $artifacts = new Artifacts([
            $object
        ]);
        $artifacts->set($object);
        $this->assertSame($object, $artifacts->get(stdClass::class));
    }

    public function testCanBeIterated()
    {
        $object = new stdClass();
        $artifacts = new Artifacts([
            $object
        ]);
        $this->assertIsIterable($artifacts);
    }

    public function testArtifactsCanBeOverwritten()
    {
        $object1 = new stdClass();
        $object2 = new stdClass();
        $artifacts = new Artifacts();
        $artifacts->set($object1);
        $artifacts->set($object2);
        $this->assertNotSame($object1, $artifacts->get(stdClass::class));
        $this->assertSame($object2, $artifacts->get(stdClass::class));
    }

    public function testThrowsExceptionWhenTryingToGetNonExistingArtifact()
    {
        $this->expectException(ArtifactNotFound::class);

        $artifacts = new Artifacts();
        $artifacts->get(stdClass::class);
    }

    public function testSpawnsNewContainerMergingGivenArtifacts()
    {
        $object1 = new class extends stdClass {
        };
        $object2 = new class extends stdClass {
        };
        $object3 = new class extends stdClass {
        };

        $artifacts1 = new Artifacts([$object1]);
        $artifacts2= $artifacts1->spawnMutated(new Artifacts([
            $object2,
            $object3
        ]));

        $this->assertNotSame($artifacts1, $artifacts2);
        $this->assertSame($object1, $artifacts2->get(get_class($object1)));
    }
}
