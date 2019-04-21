<?php

namespace Maestro\Model\Job;

use Amp\Promise;

interface JobDispatcher
{
    public function dispatch(Job $job): Promise;
}
