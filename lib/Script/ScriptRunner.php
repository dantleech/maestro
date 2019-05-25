<?php

namespace Maestro\Script;

use Amp\Process\Process;
use Amp\Promise;
use Generator;
use Maestro\Loader\Instantiator;
use Maestro\Task\Artifacts;
use Maestro\Util\StringUtil;

class ScriptRunner
{
    public function run(string $script, string $workingDirectory, array $env): Promise
    {
        return \Amp\call(function () use ($script, $workingDirectory, $env) {
            $process = new Process($script, $workingDirectory, $env);
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
        ] as $type => $stream) {

            $outs[] = \Amp\call(function () use ($stream) {
                $lastLine = '';
                $buffer = '';
                while (null !== $chunk = yield $stream->read()) {
                    $buffer .= $chunk;
                }

                return StringUtil::lastLine($buffer);
            });
        }
        
        return yield $outs;
    }
}
