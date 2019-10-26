<?php

namespace Maestro\Library\Loader\Loader;

use Maestro\Library\Loader\Loader;
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
                $includePath = $this->resolvePath($value, $parentResource);
                unset($data[self::KEY_INCLUDE]);

                $data = array_merge($data, $this->inflate(
                    $this->innerLoader->load($includePath),
                    $includePath
                ));
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
                $path, $parentPath
            ));
        }

        return Path::makeAbsolute($path, Path::getDirectory($parentPath));
    }
}
