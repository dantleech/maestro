<?php

namespace Maestro\Library\GraphTask\Exception;

use RuntimeException;

class ArtifactNotFound extends RuntimeException
{
    public function __construct(string $artifactFqn, array $availableArtifacts)
    {
        parent::__construct(sprintf(
            'No artifact with class "%s" has been set, known artifacts: "%s"',
            $artifactFqn,
            implode('", "', $availableArtifacts)
        ));
    }
}