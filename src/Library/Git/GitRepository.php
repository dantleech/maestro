<?php

namespace Maestro\Library\Git;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Git\Exception\GitException;
use Maestro\Library\Script\ScriptResult;
use Maestro\Library\Script\ScriptRunner;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Vcs\Exception\CheckoutError;
use Maestro\Library\Vcs\Repository;
use Psr\Log\LoggerInterface;
use Maestro\Library\Vcs\Tag;
use Maestro\Library\Vcs\Tags;

class GitRepository implements Repository
{
    /**
     * @var ScriptRunner
     */
    private $runner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $path;

    public function __construct(ScriptRunner $runner, LoggerInterface $logger, string $path)
    {
        $this->runner = $runner;
        $this->logger = $logger;
        $this->path = $path;
    }

    public function isCheckedOut(): bool
    {
        return file_exists($this->path . '/.git');
    }

    /**
     * {@inheritDoc}
     */
    public function checkout(string $url, Environment $environment): Promise
    {
        return \Amp\call(function () use ($url, $environment) {
            $result = yield $this->runner->run(sprintf(
                'git clone %s %s',
                $url,
                $this->path,
            ), dirname($this->path), $environment->toArray());

            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new CheckoutError(sprintf(
                    'Could not clone "%s" to "%s": %s',
                    $url,
                    $this->path,
                    $result->stderr()
                ));
            }

            return new Success();
        });
    }

    public function listTags(): Promise
    {
        return \Amp\call(function () {
            $result = yield $this->runner->run('git tag --format="%(refname:strip=2) %(objectname)"', $this->path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not list tags in "%s"',
                    $this->path
                ));
            }

            return new Tags(array_map(function ($tag) {
                return new Tag($tag[0], $tag[1]);
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

    public function tag(string $version): Promise
    {
        return \Amp\call(function () use ($version) {
            $result = yield $this->runner->run(sprintf('git tag %s', $version), $this->path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                if (strpos($result->stderr(), 'already exists')) {
                    $this->logger->info('Ignoring already existing tag');
                    return $result;
                }

                throw new GitException(sprintf(
                    'Could not list tags in "%s": %s',
                    $this->path,
                    $result->stderr()
                ));
            }

            $this->logger->info(sprintf('Tagged "%s"', $version));

            return $result;
        });
    }

    public function headId(): Promise
    {
        return \Amp\call(function () {
            $result = yield $this->runner->run('git rev-parse HEAD', $this->path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not parse current revision in "%s"',
                    $this->path
                ));
            }

            return trim($result->stdout());
        });
    }

    public function commitsBetween(string $start, string $end): Promise
    {
        return \Amp\call(function () use ($start, $end) {
            $result = yield $this->runner->run(
                sprintf(
                    'git rev-list %s...%s',
                    $start,
                    $end
                ),
                $this->path,
                []
            );

            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not list commit Ids between "%s" and "%s" in "%s"',
                    $start,
                    $end,
                    $this->path
                ));
            }

            return array_filter(array_map('trim', explode("\n", $result->stdout())));
        });
    }

    public function message(string $commitId): Promise
    {
        return \Amp\call(function () use ($commitId) {
            $result = yield $this->runner->run(sprintf('git log %s -1 --pretty=%%B', $commitId), $this->path, []);
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new GitException(sprintf(
                    'Could not read commit message for "%s" in "%s"',
                    $commitId,
                    $this->path
                ));
            }

            return trim($result->stdout());
        });
    }
}
