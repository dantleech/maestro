<?php

namespace Maestro\Model\Unit;

final class Parameters
{
    public function mergeArray(array $parameters): self
    {
        return new self();
    }

    public function copy(): self
    {
        return new self();
    }

    public static function new()
    {
        return new self();
    }
}
