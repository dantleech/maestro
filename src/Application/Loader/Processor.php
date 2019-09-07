<?php

namespace Maestro\Application\Loader;

interface Processor
{
    public function process(array $manifest): array;
}
