<?php

namespace Maestro\Library\Loader\Loader;

use Maestro\Library\Loader\Exception\LoaderError;
use Maestro\Library\Loader\Loader;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;

class IncludingLoader implements Loader
{
    private const KEY_INCLUDE = '_include';

    /**
     * @var Loader
     */
    private $innerLoader;

    public function __construct(Loader $innerLoader)
    {
        $this->innerLoader = $innerLoader;
    }

    public function load(string $resource): array
    {
        $data = $this->innerLoader->load($resource);
        $data = $this->inflate($data, $resource);

        return $data;
    }

    private function inflate(array $data, string $parentResource): array
    {
        foreach ($data as $key => $value) {
            if ($key === self::KEY_INCLUDE) {
                $data = $this->processInclude($value, $parentResource, $data);
            }

            if (is_array($value)) {
                $data[$key] = $this->inflate($value, $parentResource);
            }
        }

        return $data;
    }

    private function resolvePath(string $path, string $parentPath): string
    {
        if (Path::isAbsolute($path)) {
            throw new LoaderError(sprintf(
                'Absolute paths not permitted when including config: "%s" in "%s"',
                $path,
                $parentPath
            ));
        }

        return Path::makeAbsolute($path, Path::getDirectory($parentPath));
    }

    private function processInclude(string $includePath, string $parentResource, array $data): array
    {
        unset($data[self::KEY_INCLUDE]);
        $includePath = $this->resolvePath($includePath, $parentResource);
        if (!Glob::isDynamic($includePath)) {
            return $this->importPath($includePath, $data);
        }

        return array_map(function (string $path) use ($data) {
            return $this->importPath($path, $data);
        }, Glob::glob($includePath));
    }

    private function importPath(string $includePath, array $data)
    {
        return array_merge($data, $this->inflate(
            $this->innerLoader->load($includePath),
            $includePath
        ));
    }
}
