<?php

namespace Maestro\Library\Workspace;

interface PathStrategy
{
    public function packageNameToSubPath(string $packageName): string;

    public function listingGlobPattern(): string;

    public function subPathToPackageName(string $string);
}
