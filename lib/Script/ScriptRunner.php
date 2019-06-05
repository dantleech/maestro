<?php

namespace Maestro\Script;

use Amp\Process\Process;
use Amp\Promise;
use Generator;
use Maestro\Loader\Instantiator;
use Maestro\Util\StringUtil;
use Psr\Log\LoggerInterface;

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
            $process = new Process($script, $workingDirectory, $env);
            $pid  = yield $process->start();
            $this->logger->info(sprintf('Process started: PID: %s Script:%s in %s', $pid, $script, $workingDirectory));

            $outs = yield from $this->handleStreamOutput($process);
            $exitCode = yield $process->join();

            if ($exitCode !== 0) {
                $this->logger->error(sprintf('Process %s "%s" exited with %s: %s', $pid, $script, $exitCode, $outs[1]));
            }

            return Instantiator::create()->instantiate(ScriptResult::class, [
                'exitCode' => $exitCode,
                'lastStdout' => $outs[0],
                'lastStderr' => $outs[1],
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

                return StringUtil::lastLine($buffer);
            });
        }
        
        return yield $outs;
    }
}
