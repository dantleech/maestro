<?php

namespace Maestro\Extension\Process\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Generator;
use Maestro\Extension\Process\Job\Exception\ProcessNonZeroExitCode;
use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Util\StringUtil;

class ProcessHandler
{
    const MAX_LASTLINE_LENGTH = 255;

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
            $this->consoleManager->stdout($job->consoleId())->writeln('# ' . $job->command());
            $process = new AmpProcess(
                $job->command(),
                $job->workingDirectory()
            );

            yield $process->start();
            $outs = yield from $this->handleStreamOutput($process, $job);
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

    private function handleStreamOutput(AmpProcess $process, Process $job): Generator
    {
        $outs = [];

        foreach ([
            [ $process->getStdout(), $this->consoleManager->stdout($job->consoleId()) ],
            [ $process->getStderr(), $this->consoleManager->stderr($job->consoleId()) ],
        ] as $streamConsole) {
            [ $stream, $console ] = $streamConsole;

            $outs[] = \Amp\call(function () use ($stream, $console) {
                $buffer = '';
                $lastLine = '';
                while (null !== $chunk = yield $stream->read()) {
                    $buffer .= $chunk;
                    if (false !== $offset = strrpos($buffer, "\n")) {
                        $console->writeln(trim(substr($buffer, 0, $offset)));
                        $lastLine = StringUtil::lastLine($buffer);
                        $buffer = substr($buffer, $offset + 1);
                    }
                }

                if ($buffer) {
                    $console->write($buffer);
                }

                return $lastLine;
            });
        }
        
        return yield $outs;
    }
}
