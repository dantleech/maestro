<?php

namespace Maestro\Console\Progress;

interface Progress
{
    public function render(): ?string;
}
