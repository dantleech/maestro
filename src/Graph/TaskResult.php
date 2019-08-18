<?php

namespace Maestro\Graph;

class TaskResult
{
    private const STATE_PENDING = 'busy';
    private const STATE_SUCCESS = 'succeeded';
    private const STATE_FAIL = 'failed';

    /**
     * @var string
     */
    private $state;

    private function __construct(string $state)
    {
        $this->state = $state;
    }

    public static function SUCCESS()
    {
        return new self(self::STATE_SUCCESS);
    }

    public static function FAILURE()
    {
        return new self(self::STATE_FAIL);
    }

    public static function PENDING()
    {
        return new self(self::STATE_PENDING);
    }

    public function is(TaskResult $taskResult): bool
    {
        return $taskResult->state === $this->state;
    }

    public function toString(): string
    {
        return $this->state;
    }
}
