<?php

namespace Maestro\Tests\Unit\Library\Git;

use Maestro\Extension\Git\Model\ExistingTags;
use Maestro\Extension\Git\Model\Git;
use Maestro\Library\Git\GitRepository;
use Maestro\Library\Script\ScriptRunner;
use Maestro\Tests\IntegrationTestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Process\Process;
use function Amp\Promise\wait;

class GitRepositoryTest extends IntegrationTestCase
{
    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    /**
     * @var GitRepository
     */
    private $gitRepository;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README.md', 'Hello');

        $this->exec('git init');
        $this->exec('git add README.md');
        $this->exec('git commit -m "Initial"');

        $logger = new NullLogger();

        $this->scriptRunner = new ScriptRunner($logger);
        $this->gitRepository = new GitRepository(
            $this->scriptRunner,
            $logger,
            $this->workspace()->path('/')
        );
    }

    /**
     * @dataProvider provideListTags
     */
    public function testListTags(array $tags, ?array $expectedTags = null)
    {
        if (null === $expectedTags) {
            $expectedTags = $tags;
        }

        foreach ($tags as $tag) {
            $this->exec('git tag '.$tag);
        }

        $this->assertEquals(
            $expectedTags,
            wait($this->gitRepository->listTags())->names()
        );
    }

    public function provideListTags()
    {
        yield 'empty' => [
            []
        ];

        yield 'single tag' => [
            [ '1.0.0' ],
        ];

        yield 'multiple tags 1' => [
            [ '1.0.0', '1.0.1' ],
        ];

        yield 'sorts tags 1' => [
            [ '1', '3', '2' ],
            [ '1', '2', '3' ],
        ];

        yield 'sorts tags 2' => [
            [ '0.1.2', '0.1.0', '1.1.1', '100' ],
            [ '0.1.0', '0.1.2', '1.1.1' , '100'],
        ];
    }

    public function testTagsNewTag()
    {
        wait($this->gitRepository->tag('1.0.0'));
        $this->assertEquals([
            '1.0.0'
        ], wait($this->gitRepository->listTags())->names());
    }

    public function testIgnoresExistingTag()
    {
        $this->exec('git tag 1.0.0');

        wait(
            $this->gitRepository->tag('1.0.0')
        );

        $this->assertEquals(['1.0.0'], wait($this->gitRepository->listTags())->names());
    }

    public function testGetsHeadId()
    {
        $headId = wait($this->gitRepository->headId());
        $this->assertNotNull($headId);
    }

    public function testExistingTagsIncludeCommitId()
    {
        $this->exec('git tag 1.0.0');
        $tags = wait($this->gitRepository->listTags());
        $this->assertCount(1, $tags);
        assert($tags instanceof ExistingTags);
        $tag = $tags->mostRecent();
        $this->assertEquals(
            wait($this->gitRepository->headId($this->workspace()->path('/'))),
            $tag->commitId()
        );
    }

    public function testCommitsBetween()
    {
        $this->exec('git tag 1.0.0');
        $this->workspace()->put('foobar1', '');
        $this->exec('git add foobar1');
        $this->exec('git commit -m "foobar1"');
        $this->workspace()->put('foobar2', '');
        $this->exec('git add foobar2');
        $this->exec('git commit -m "foobar2"');

        $commitIds = wait($this->gitRepository->commitsBetween(
            '1.0.0',
            wait($this->gitRepository->headId($this->workspace()->path('/')))
        ));
        $this->assertCount(2, $commitIds);
    }

    public function testComment()
    {
        $this->exec('git tag 1.0.0');
        $this->workspace()->put('foobar1', '');
        $this->exec('git add foobar1');
        $this->exec('git commit -m "Hello World"');

        $message = wait($this->gitRepository->message(
            wait($this->gitRepository->headId())
        ));
        $this->assertEquals('Hello World', $message);
    }

    private function exec(string $string): Process
    {
        $process = new Process($string, $this->workspace()->path('/'));
        $process->run();

        if ($process->getExitCode() !== 0) {
            throw new RuntimeException(
                sprintf(
                    'Could not exec process "%s": %s%s',
                    $process->getCommandLine(),
                    $process->getOutput(),
                    $process->getErrorOutput()
                )
            );
        }

        return $process;
    }
}
