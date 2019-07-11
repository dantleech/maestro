<?php

namespace Maestro\Workspace;

interface PathStrategy
{
    public function packageNameToPath(string $packageName): string;

    public function listingGlobPattern(): string;
}
