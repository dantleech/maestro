<?php

namespace Maestro\Model\Job;

use Amp\Promise;

interface Job
{
    public function handler(): string;
}
