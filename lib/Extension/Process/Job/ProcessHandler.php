<?php

namespace Maestro\Extension\Process\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Generator;
use Maestro\Extension\Process\Job\Exception\ProcessNonZeroExitCode;
use Maestro\Model\Tty\TtyManager;
use Maestro\Model\Util\StringUtil;

class ProcessHandler
{
    const MAX_LASTLINE_LENGTH = 255;

    /**
     * @var TtyManager
     */
    private $ttyManager;

    public function __construct(TtyManager $ttyManager)
    {
        $this->ttyManager = $ttyManager;
    }

    public function __invoke(Process $job): Promise
    {
        return \Amp\call(function (Process $job) {
            $this->ttyManager->stdout($job->ttyId())->writeln('# ' . $job->command());
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
            [ $process->getStdout(), $this->ttyManager->stdout($job->ttyId()) ],
            [ $process->getStderr(), $this->ttyManager->stderr($job->ttyId()) ],
        ] as $streamTty) {
            [ $stream, $tty ] = $streamTty;

            $outs[] = \Amp\call(function () use ($stream, $tty) {
                $buffer = '';
                $lastLine = '';
                while (null !== $chunk = yield $stream->read()) {
                    $buffer .= $chunk;
                    if (false !== $offset = strrpos($buffer, "\n")) {
                        $tty->writeln(trim(substr($buffer, 0, $offset)));
                        $lastLine = StringUtil::lastLine($buffer);
                        $buffer = substr($buffer, $offset + 1);
                    }
                }

                if ($buffer) {
                    $tty->write($buffer);
                }

                return $lastLine;
            });
        }
        
        return yield $outs;
    }
}
