<?php

namespace Maestro\Workspace\PathStrategy;

use Maestro\Workspace\PathStrategy;

class NestedDirectoryStrategy implements PathStrategy
{
    public function packageNameToSubPath(string $packageName): string
    {
        return $packageName;
    }

    public function listingGlobPattern(): string
    {
        return '*/*';
    }

    public function subPathToPackageName(string $subPath)
    {
        return $subPath;
    }
}
