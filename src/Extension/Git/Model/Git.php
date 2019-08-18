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
            $result = yield $this->runner->run('git tag --format="%(refname:strip=2) %(objectname)"', $path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not list tags in "%s"',
                    $path
                ));
            }

            return new ExistingTags(array_map(function ($tag) {
                return new ExistingTag($tag[0], $tag[1]);
            }, array_filter(
                array_map(
                    function (string $line) {
                        return array_filter(array_map(
                            'trim',
                            explode(' ', $line)
                        ));
                    },
                    explode(
                        "\n",
                        $result->stdout()
                    )
                )
            )));
        });
    }

    public function tag(string $path, string $version): Promise
    {
        return \Amp\call(function () use ($path, $version) {
            $result = yield $this->runner->run(sprintf('git tag %s', $version), $path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                if (strpos($result->stderr(), 'already exists')) {
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

    public function headId(string $path): Promise
    {
        return \Amp\call(function () use ($path) {
            $result = yield $this->runner->run('git rev-parse HEAD', $path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not parse current revision in "%s"',
                    $path
                ));
            }

            return trim($result->stdout());
        });
    }

    public function commitsBetween(string $path, string $start, string $end): Promise
    {
        return \Amp\call(function () use ($path, $start, $end) {
            $result = yield $this->runner->run(
                sprintf(
                    'git rev-list %s...%s',
                    $start,
                    $end
                ),
                $path,
                []
            );

            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not list commit Ids between "%s" and "%s" in "%s"',
                    $start,
                    $end,
                    $path
                ));
            }

            return array_filter(array_map('trim', explode("\n", $result->stdout())));
        });
    }
}
