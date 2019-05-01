<?php

namespace Maestro\Model\Job\Test;

use Maestro\Model\Job\Dispatcher\EagerDispatcher;
use Maestro\Model\Job\Dispatcher\LazyDispatcher;
use Maestro\Model\Job\Job;

final class HandlerTester
{
    /**
     * @var EagerDispatcher
     */
    private $dispatcher;

    public static function create(): self
    {
        return new self();
    }

    public function dispatch(Job $job, callable $handler)
    {
        $dispatcher = new LazyDispatcher([
            get_class($job) => function () use ($handler) {
                return $handler;
            }
        ]);

        return \Amp\Promise\wait($dispatcher->dispatch($job));
    }
}
