<?php

namespace Maestro\Tests\Unit\Extension\Task\Serializer\Normalizer;

use Maestro\Extension\Task\Serializer\Normalizer\ArtifactNormalizer;
use Maestro\Library\Artifact\Artifact;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use stdClass;

class ArtifactNormalizerTest extends TestCase
{
    public function testArtifactNormalizer()
    {
        $artifact = new class implements Artifact {
            private $foo = 'bar';
        };

        $normalizer = new ArtifactNormalizer(new PropertyNormalizer());
        $this->assertTrue($normalizer->supportsNormalization($artifact));
        $this->assertFalse($normalizer->supportsNormalization(new stdClass()));
        $this->assertEquals([
            'class' => get_class($artifact),
            'data' => [
                'foo' => 'bar',
            ],
        ], $normalizer->normalize($artifact));
    }
}
