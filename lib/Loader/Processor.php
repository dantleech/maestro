<?php

namespace Maestro\Loader;

interface Processor
{
    public function process(array $manifest): array;
}
