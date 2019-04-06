<?php

namespace Phpactor\Extension\Maestro\Model;

use Phpactor\Extension\Maestro\Model\Exception\HandlerNotFound;

class JobHandlerRegistry
{
    /**
     * @var array
     */
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $name => $handler) {
            $this->add($name, $handler);
        }
    }

    public function get(string $name)
    {
        if (!isset($this->handlers[$name])) {
            throw new HandlerNotFound(sprintf(
                'Handler "%s" not found, known handlers: "%s"',
                $name, implode('", "', array_keys($this->handlers))
            ));
        }

        return $this->handlers[$name];
    }

    private function add($name, JobHandler $handler): void
    {
        $this->handlers[$name] = $handler;
    }
}
