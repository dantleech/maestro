<?php

namespace Maestro\Library\Loader;

interface Loader
{
    public function load(string $resource): array;
}
