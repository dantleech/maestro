<?php

namespace Maestro\Loader\Processor;

use Maestro\Loader\Processor;
use Maestro\Loader\AliasToClassMap;

class LoaderAliasExpandingProcessor implements Processor
{
    /**
     * @var AliasToClassMap
     */
    private $loaderMap;

    public function __construct(AliasToClassMap $loaderMap)
    {
        $this->loaderMap = $loaderMap;
    }

    public function process(array $manifest): array
    {
        foreach ($manifest['packages'] ?? [] as $packageName => &$package) {
            foreach ($package['loaders'] ?? [] as $loaderName => &$loader) {
                $manifest['packages'][$packageName]['loaders'][$loaderName]['type'] = $this->loaderMap->classNameFor($loader['type']);
            }
        }
        return $manifest;
    }
}
