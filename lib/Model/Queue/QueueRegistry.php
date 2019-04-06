<?php

namespace Phpactor\Extension\Maestro\Model\Queue;

use Phpactor\Extension\Maestro\Model\Exception\QueueAlreadyExists;
use Phpactor\Extension\Maestro\Model\Exception\QueueNotFound;
use Phpactor\Extension\Maestro\Model\Queue\Queue;

class QueueRegistry
{
    private $queues = [];

    public function get(string $name): Queue
    {
        if (!isset($this->queues[$name])) {
            throw new QueueNotFound(sprintf(
                'Job queue "%s" not found, known job queues: "%s"',
                $name, implode('", "', array_keys($this->queues))
            ));
        }

        return $this->queues[$name];
    }

    public function createQueue($name): Queue
    {
        if (isset($this->queues[$name])) {
            throw new QueueAlreadyExists(sprintf(
                'Queue "%s" already exists',
                $name
            ));
        }

        $this->queues[$name] = new Queue();

        return $this->queues[$name];
    }

    /**
     * @return Queue[]
     */
    public function all(): array
    {
        return $this->queues;
    }
}
