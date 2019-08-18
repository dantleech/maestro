<?php

namespace Maestro\Extension\Git\Model;

use Amp\Promise;
use Maestro\Extension\Git\Model\Exception\GitException;
use Maestro\Script\ScriptResult;
use Maestro\Script\ScriptRunner;
use Psr\Log\LoggerInterface;

class Git
{
    /**
     * @var ScriptRunner
     */
    private $runner;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ScriptRunner $runner, LoggerInterface $logger)
    {
        $this->runner = $runner;
        $this->logger = $logger;
    }

    public function listTags(string $path): Promise
    {
        return \Amp\call(function () use ($path) {
            $result = yield $this->runner->run('git tag', $path, []);
            assert($result instanceof ScriptResult);
            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not list tags in "%s"',
                    $path
                ));
            }

            return array_filter(array_map('trim', explode("\n", $result->lastStdout())));
        });
    }

    public function tag(string $path, string $version): Promise
    {
        return \Amp\call(function () use ($path, $version) {
            $result = yield $this->runner->run(sprintf('git tag %s', $version), $path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                if (strpos($result->lastStderr(), 'already exists')) {
                    $this->logger->info('Ignoring already existing tag');
                    return $result;
                }

                throw new GitException(sprintf(
                    'Could not list tags in "%s"',
                    $path
                ));
            }

            $this->logger->info(sprintf('Tagged "%s"', $version));

            return $result;
        });
    }
}
