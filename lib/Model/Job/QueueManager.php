<?php

namespace Maestro\Model\Job;

class QueueManager
{
    /**
     * @var Queue[]
     */
    private $queues = [];

    public function getOrCreate(string $id): Queue
    {
        if (isset($this->queues[$id])) {
            return $this->queues[$id];
        }

        $this->queues[$id] = new Queue($id);

        return $this->queues[$id];
    }
}
