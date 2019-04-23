<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Maestro\Adapter\Amp\Job\Exception\ProcessNonZeroExitCode;
use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Util\StringUtil;

class ProcessHandler
{
    /**
     * @var ConsoleManager
     */
    private $consoleManager;

    public function __construct(ConsoleManager $consoleManager)
    {
        $this->consoleManager = $consoleManager;
    }

    public function __invoke(Process $job): Promise
    {
        return \Amp\call(function (Process $job) {
            $this->consoleManager->stdout($job->consoleId())->writeln('EXEC: ' . $job->command());
            $process = new AmpProcess(
                $job->command(),
                $job->workingDirectory()
            );

            yield $process->start();

            $outs = [];
            foreach ([
                [ $process->getStdout(), $this->consoleManager->stdout($job->consoleId()) ],
                [ $process->getStderr(), $this->consoleManager->stderr($job->consoleId()) ],
            ] as $streamConsole) {
                [ $stream, $console ] = $streamConsole;
                $outs[] = \Amp\call(function () use ($stream, $console) {
                    $lastLine = '';

                    while (null !== $chunk = yield $stream->read()) {
                        $console->write($chunk);
                        $lastLine .= $chunk;
                    }

                    return StringUtil::extractAfterNewline($lastLine);
                });
            }

            $outs = yield $outs;
            $exitCode = yield $process->join();

            if ($exitCode !== 0) {
                throw new ProcessNonZeroExitCode(sprintf(
                    '%s%s',
                    $outs[0],
                    $outs[1]
                ), $exitCode);
            }

            return $outs[0];
        }, $job);
    }
}
