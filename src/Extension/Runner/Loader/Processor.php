<?php

namespace Maestro\Extension\Runner\Loader;

interface Processor
{
    public function process(array $manifest): array;
}
