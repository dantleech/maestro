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

            $this->logger->debug(sprintf('Script "%s" in %s with %s', $script, $workingDirectory, json_encode($env)));
            $pid  = yield $process->start();

            $outs = yield from $this->handleStreamOutput($process);
            $exitCode = yield $process->join();

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
            $process->getStdout(),
            $process->getStderr(),
            ] as $stream) {
            $outs[] = \Amp\call(function () use ($stream) {
                $lastLine = '';
                $buffer = '';
                while (null !== $chunk = yield $stream->read()) {
                    $buffer .= $chunk;
                }

                $this->logger->debug($buffer);

                return StringUtil::lastLine($buffer);
            });
        }
        
        return yield $outs;
    }
}
