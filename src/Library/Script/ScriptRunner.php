<?php

namespace Maestro\Library\Script;

use Amp\Process\Process;
use Amp\Promise;
use Generator;
use Maestro\Library\Instantiator\Instantiator;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ScriptRunner
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run(string $script, string $workingDirectory, array $env): Promise
    {
        return \Amp\call(function () use ($script, $workingDirectory, $env) {
            $env = array_merge(getenv(), $env);

            if (!file_exists($workingDirectory)) {
                throw new RuntimeException(sprintf('Working directory "%s" does not exist (trying to run "%s")', $workingDirectory, $script));
            }

            $process = new Process($script, $workingDirectory, $env);
            $pid  = yield $process->start();
            $this->logger->debug(sprintf('process "%s" running in "%s": %s', $pid, basename($workingDirectory), $script));

            $outs = yield from $this->handleStreamOutput($process);
            $exitCode = yield $process->join();

            if ($exitCode !== 0) {
                $this->logger->error(sprintf('process %s in "%s" "%s" exited with %s: %s', $pid, basename($workingDirectory), $script, $exitCode, $outs[1]));
            }

            return Instantiator::instantiate(ScriptResult::class, [
                'exitCode' => $exitCode,
                'stdout' => $outs[0],
                'stderr' => $outs[1],
            ]);
        });
    }

    private function handleStreamOutput(Process $process): Generator
    {
        $outs = [];
        foreach ([
            'STDOUT' => $process->getStdout(),
            'STDERR' => $process->getStderr(),
            ] as $name => $stream) {
            $outs[] = \Amp\call(function () use ($name, $stream) {
                $lastLine = '';
                $buffer = '';
                while (null !== $chunk = yield $stream->read()) {
                    $buffer .= $chunk;
                }

                if ($buffer) {
                    $this->logger->debug(sprintf('%s: %s', $name, $buffer));
                }

                return $buffer;
            });
        }
        
        return yield $outs;
    }
}
