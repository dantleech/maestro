<?php

namespace Maestro\Extension\Tmux\Model;

use Maestro\Extension\Tmux\Model\Exception\TmuxFailure;
use RuntimeException;

class TmuxClient
{
    /**
     * @var string|null
     */
    private $socketPath;

    public function __construct(string $socketPath = null)
    {
        $this->socketPath = $socketPath;
    }

    public function listSessions()
    {
        return array_filter(array_map(function (string $line) {
            return substr($line, 0, (int) strpos($line, ':'));
        }, explode("\n", $this->cmd(['list-sessions']))));
    }

    public function createSession(string $name, string $workingDirectory): void
    {
        $this->cmd([
            'new-session',
            '-d',
            sprintf('-c%s', $workingDirectory),
            sprintf('-s%s', escapeshellarg($name))
        ]);
    }

    public function switchTo(string $name)
    {
        $this->cmd([
            'switch-client',
            sprintf('-t%s', escapeshellarg($name))
        ]);
    }

    public function isInside(): bool
    {
        return (bool) getenv('TMUX');
    }

    private function cmd(array $args): string
    {
        $command = 'tmux ' . $this->buildArgs($args);
        $process = proc_open(
            $command,
            [
                ['pipe', 'r'],
                ['pipe', 'w'],
                ['pipe', 'w'],
            ],
            $pipes
        );

        if (false === $process) {
            throw new RuntimeException(sprintf(
                'Could not open process "%s"',
                $command
            ));
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new TmuxFailure($exitCode, (string) $stderr);
        }

        return (string) $stdout;
    }

    private function buildArgs(array $args): string
    {
        $baseArgs = [];
        if ($this->socketPath) {
            $baseArgs[] = sprintf('-S %s', escapeshellarg($this->socketPath));
        }

        return implode(' ', array_merge($baseArgs, $args));
    }
}
