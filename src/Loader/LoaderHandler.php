<?php

namespace Maestro\Loader;

use Maestro\Node\GraphBuilder;

interface LoaderHandler
{
    public function load(string $parentId, GraphBuilder $builder, Loader $loader): void;
}
