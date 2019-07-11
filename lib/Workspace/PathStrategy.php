<?php

namespace Maestro\Workspace;

interface PathStrategy
{
    public function packageNameToPath(string $packageName): string;
}
