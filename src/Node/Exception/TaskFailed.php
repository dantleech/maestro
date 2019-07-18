<?php

namespace Maestro\Node\Exception;

use Maestro\Node\Artifacts;
use RuntimeException;

class TaskFailed extends RuntimeException
{
    /**
     * @var Artifacts
     */
    private $artifacts;

    public function __construct(string $message, Artifacts $artifacts = null)
    {
        $this->artifacts = $artifacts ?: Artifacts::create([]);
        parent::__construct($message);
        $this->message = $message;
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }
}
