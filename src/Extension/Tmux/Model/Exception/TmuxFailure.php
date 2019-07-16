<?php

namespace Maestro\Extension\Tmux\Model\Exception;

use RuntimeException;

class TmuxFailure extends RuntimeException
{
    /**
     * @var int
     */
    private $exitCode;

    /**
     * @var string
     */
    private $stderr;


    public function __construct(int $exitCode, string $stderr)
    {
        parent::__construct(sprintf(
            'Tmux failed with status "%s": %s',
            $exitCode,
            $stderr
        ));
        $this->exitCode = $exitCode;
        $this->stderr = $stderr;
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }

    public function stderr(): string
    {
        return $this->stderr;
    }
}
