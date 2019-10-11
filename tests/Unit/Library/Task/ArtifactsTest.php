<?php

namespace Maestro\Tests\Unit\Library\Task;

use Maestro\Library\Task\Artifact;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Exception\ArtifactNotFound;
use PHPUnit\Framework\TestCase;

class ArtifactsTest extends TestCase
{
    public function testSetAndGetObject()
    {
        $object = new TestArtifact();
        $artifacts = new Artifacts();
        $artifacts->set($object);
        $this->assertSame($object, $artifacts->get(TestArtifact::class));
    }

    public function testCreateFromArrayOfObjects()
    {
        $object = new TestArtifact();
        $artifacts = new Artifacts([
            $object
        ]);
        $artifacts->set($object);
        $this->assertSame($object, $artifacts->get(TestArtifact::class));
    }

    public function testCanBeIterated()
    {
        $object = new TestArtifact();
        $artifacts = new Artifacts([
            $object
        ]);
        $this->assertIsIterable($artifacts);
    }

    public function testArtifactsCanBeOverwritten()
    {
        $object1 = new TestArtifact();
        $object2 = new TestArtifact();
        $artifacts = new Artifacts();
        $artifacts->set($object1);
        $artifacts->set($object2);
        $this->assertNotSame($object1, $artifacts->get(TestArtifact::class));
        $this->assertSame($object2, $artifacts->get(TestArtifact::class));
    }

    public function testThrowsExceptionWhenTryingToGetNonExistingArtifact()
    {
        $this->expectException(ArtifactNotFound::class);

        $artifacts = new Artifacts();
        $artifacts->get(TestArtifact::class);
    }

    public function testSpawnsNewContainerMergingGivenArtifacts()
    {
        $object1 = new TestArtifact();
        $object2 = new TestArtifact2();
        $object3 = new TestArtifact3();

        $artifacts1 = new Artifacts([$object1]);
        $artifacts2= $artifacts1->spawnMutated(new Artifacts([
            $object2,
            $object3
        ]));

        $this->assertNotSame($artifacts1, $artifacts2);
        $this->assertSame($object1, $artifacts2->get(get_class($object1)));
    }
}

class TestArtifact implements Artifact
{
}
class TestArtifact2 extends TestArtifact
{
}
class TestArtifact3 extends TestArtifact
{
}
