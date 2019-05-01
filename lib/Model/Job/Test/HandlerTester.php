<?php

namespace Maestro\Model\Job\Test;

use Maestro\Model\Job\Dispatcher\EagerDispatcher;
use Maestro\Model\Job\Dispatcher\LazyDispatcher;
use Maestro\Model\Instantiator;

final class HandlerTester
{
    /**
     * @var EagerDispatcher
     */
    private $dispatcher;

    /**
     * @var callable
     */
    private $handler;

    private function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public static function create(callable $handler): self
    {
        return new self($handler);
    }

    public function dispatch($jobClass, array $parameters)
    {
        $dispatcher = new LazyDispatcher([
            $jobClass => function () {
                return $this->handler;
            }
        ]);

        return \Amp\Promise\wait(
            $dispatcher->dispatch(
                Instantiator::create()->instantiate(
                    $jobClass,
                    $parameters
                )
            )
        );
    }
}
