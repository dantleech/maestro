<?php

namespace Maestro\Workspace\PathStrategy;

use Maestro\Workspace\PathStrategy;

class NestedDirectoryStrategy implements PathStrategy
{
    public function packageNameToPath(string $packageName): string
    {
        return $packageName;
    }
}
