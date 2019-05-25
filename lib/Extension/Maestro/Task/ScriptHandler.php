<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Process\Process;
use Amp\Promise;
use Generator;
use Maestro\Task\Artifacts;
use Maestro\Task\TaskHandler;
use Maestro\Task\Task\ScriptTask;
use Maestro\Util\StringUtil;

class ScriptHandler implements TaskHandler
{
    public function __invoke(ScriptTask $script, Artifacts $artifacts): Promise
    {
        return \Amp\call(function () use ($script, $artifacts) {
            $path = $artifacts->get('workspace')->absolutePath();
            $env = $artifacts->get('env')->toArray();

            $process = new Process($script->script(), $path, $env);
            $pid  = yield $process->start();

            $outs = yield from $this->handleStreamOutput($process);
            $exitCode = yield $process->join();


            return Artifacts::create([
                'exit_code' => $exitCode,
                'last_line' => $outs,
            ]);
        });
    }

    private function handleStreamOutput(Process $process): Generator
    {
        $outs = [];
        foreach ([
            'out' => $process->getStdout(),
            'err' => $process->getStderr(),
        ] as $type => $stream) {

            $outs[$type] = \Amp\call(function () use ($stream) {
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
