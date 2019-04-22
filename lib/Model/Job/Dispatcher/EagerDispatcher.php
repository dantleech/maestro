<?php

namespace Maestro\Model\Job\Dispatcher;

use Amp\Promise;
use Maestro\Model\Job\Exception\HandlerNotFound;
use Maestro\Model\Job\Job;
use Maestro\Model\Job\JobDispatcher;

class EagerDispatcher implements JobDispatcher
{
    /**
     * @var callable[]
     */
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $id => $handler) {
            $this->add($id, $handler);
        }
    }

    public function dispatch(Job $job): Promise
    {
        if (!isset($this->handlers[$job->handler()])) {
            throw new HandlerNotFound(sprintf(
                'Could not find handler "%s", known handlers: "%s"',
                $job->handler(),
                implode('", "', array_keys($this->handlers))
            ));
        }

        $handler = $this->handlers[$job->handler()];
        return call_user_func($handler, $job);
    }

    private function add($id, callable $handler)
    {
        $this->handlers[$id] = $handler;
    }
}
