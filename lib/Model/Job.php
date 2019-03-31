<?php

namespace Phpactor\Extension\Maestro\Model;

use Amp\Promise;

interface Job
{
    public function handler(): string;
}
