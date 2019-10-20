<?php

namespace Maestro\Extension\Runner\Model\Loader;

interface Processor
{
    public function process(array $data): array;
}
