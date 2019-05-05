<?php

namespace Maestro\Console\Progress;

class SilentProgress implements Progress
{
    public function render(): ?string
    {
        return null;
    }
}
