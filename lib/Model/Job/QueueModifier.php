<?php

namespace Maestro\Model\Job;

interface QueueModifier
{
    public function modify(Queue $queue): Queue;
}
