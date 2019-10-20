<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Webmozart\PathUtil\Path;

class PathExpander
{
    public function expand(array $paths, ?string $parentPath)
    {
        $parentPath = $parentPath ?: '/';

        return array_map(function ($path) use ($parentPath) {
            return Path::makeAbsolute($path, $parentPath);
        }, $paths);
    }
}
