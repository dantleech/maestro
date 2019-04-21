<?php

namespace Maestro\Model\Job;

interface QueueDispatcher
{
    public function dispatch(Queues $queues): void;
}
