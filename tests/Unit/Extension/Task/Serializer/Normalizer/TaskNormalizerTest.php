<?php

namespace Maestro\Tests\Unit\Extension\Task\Serializer\Normalizer;

use Maestro\Extension\Task\Extension\TaskHandlerDefinition;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Extension\Task\Serializer\Normalizer\ArtifactNormalizer;
use Maestro\Extension\Task\Serializer\Normalizer\TaskNormalizer;
use Maestro\Library\Artifact\Artifact;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use stdClass;

class TaskNormalizerTest extends TestCase
{
    public function testArtifactNormalizer()
    {
        $task = new class implements Task {
            private $foo = 'bar';
            public function description():string { return 'hello'; }
        };
        $map = new TaskHandlerDefinitionMap([
            new TaskHandlerDefinition('serviceid', 'test', get_class($task))
        ]);

        $normalizer = new TaskNormalizer($map, new PropertyNormalizer());
        $this->assertTrue($normalizer->supportsNormalization($task));
        $this->assertFalse($normalizer->supportsNormalization(new stdClass()));
        $this->assertEquals([
            'class' => get_class($task),
            'alias' => 'test',
            'description' => 'hello',
            'args' => [
                'foo' => 'bar',
            ],
        ], $normalizer->normalize($task));
    }
}
