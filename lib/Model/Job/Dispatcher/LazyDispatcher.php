<?php

namespace Maestro\Model\Job\Dispatcher;

use Amp\Promise;
use Closure;
use Maestro\Model\Job\Exception\HandlerNotFound;
use Maestro\Model\Job\Exception\InvalidHandler;
use Maestro\Model\Job\Job;
use Maestro\Model\Job\JobDispatcher;

class LazyDispatcher implements JobDispatcher
{
    private $callbackMap = [];

    public function __construct(array $callbackMap)
    {
        $this->callbackMap = $callbackMap;
    }

    public function dispatch(Job $job): Promise
    {
        $handler = $this->resolveHandler($job);
        $result = call_user_func($handler, $job);

        if (!$result instanceof Promise) {
            throw new InvalidHandler(sprintf(
                'Handler "%s" must return an Amp\Promise, but it returned: "%s"',
                get_class($handler),
                is_object($handler) ? get_class($handler) : gettype($handler)
            ));
        }

        return $result;
    }

    private function resolveHandler(Job $job)
    {
        $handler = $job->handler();
        
        if (!isset($this->callbackMap[$handler])) {
            throw new HandlerNotFound(sprintf(
                'Handler "%s" not found, known handlers: "%s"',
                $handler,
                implode('", "', array_keys($this->callbackMap))
            ));
        }
        
        $closure = $this->callbackMap[$handler];

        if (!$closure instanceof Closure) {
            throw new InvalidHandler(sprintf(
                'Factory closure for "%s" must return a Closure, got "%s"',
                $handler,
                is_object($closure) ? get_class($closure) : gettype($closure)
            ));
        }
        $handlerObject = $closure();
        
        if (!is_callable($handlerObject)) {
            throw new InvalidHandler(sprintf(
                'Callback for handler "%s" did not return a callable, got "%s"',
                $handler,
                is_object($handlerObject) ? get_class($handlerObject) : gettype($handlerObject)
            ));
        }

        return $handlerObject;
    }
}
