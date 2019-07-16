<?php

namespace Maestro\Loader\LoaderHandlerRegistry;

use Maestro\Loader\Exception\HandlerNotFound;
use Maestro\Loader\Loader;
use Maestro\Loader\LoaderHandler;
use Maestro\Loader\LoaderHandlerRegistry;

class EagerLoaderHandlerRegistry implements LoaderHandlerRegistry
{
    /**
     * @var array
     */
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $loaderFqn => $handler) {
            $this->add($loaderFqn, $handler);
        }
    }

    public function getFor(Loader $loader): LoaderHandler
    {
        $loaderFqn = get_class($loader);
        if (!isset($this->handlers[$loaderFqn])) {
            throw new HandlerNotFound(sprintf(
                'Loader handler for "%s" not registered, handlers are registered for: "%s"',
                $loaderFqn,
                implode('", "', array_keys($this->handlers))
            ));
        }

        return $this->handlers[$loaderFqn];
    }

    private function add(string $loaderFqn, LoaderHandler $handler)
    {
        $this->handlers[$loaderFqn] = $handler;
    }
}
