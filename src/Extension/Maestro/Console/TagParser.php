<?php

namespace Maestro\Extension\Maestro\Console;

class TagParser
{
    public function parse(string $tags): array
    {
        return array_filter(array_map('trim', explode(',', $tags)));
    }
}
