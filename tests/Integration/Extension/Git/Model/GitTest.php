<?php

namespace Maestro\Tests\Integration\Extension\Git\Model;

use Maestro\Extension\Git\Model\ExistingTags;
use Maestro\Extension\Git\Model\Git;
use Maestro\Script\ScriptRunner;
use Maestro\Tests\IntegrationTestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Process\Process;
use function Amp\Promise\wait;

class GitTest extends IntegrationTestCase
{
    /**
     * @var Git
     */
    private $git;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('README.md', 'Hello');
        $this->exec('git init');
        $this->exec('git add README.md');
        $this->exec('git commit -m "Initial"');
        $this->git = new Git(new ScriptRunner(new NullLogger()), new NullLogger());
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
            $this->listTagNames()
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
        wait($this->git->tag($this->workspace()->path('/'), '1.0.0'));
        $this->assertEquals([
            '1.0.0'
        ], $this->listTagNames());
    }

    public function testIgnoresExistingTag()
    {
        $this->exec('git tag 1.0.0');

        wait(
            $this->git->tag($this->workspace()->path('/'), '1.0.0')
        );

        $this->assertEquals(['1.0.0'], $this->listTagNames());
    }

    public function testGetsHeadId()
    {
        $headId = wait($this->git->headId($this->workspace()->path('/')));
        $this->assertNotNull($headId);
    }

    public function testExistingTagsIncludeCommitId()
    {
        $this->exec('git tag 1.0.0');
        $tags = wait($this->git->listTags($this->workspace()->path('/')));
        $this->assertCount(1, $tags);
        assert($tags instanceof ExistingTags);
        $tag = $tags->mostRecent();
        $this->assertEquals(
            wait($this->git->headId($this->workspace()->path('/'))),
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

        $commitIds = wait($this->git->commitsBetween(
            $this->workspace()->path('/'),
            '1.0.0',
            wait($this->git->headId($this->workspace()->path('/')))
        ));
        $this->assertCount(2, $commitIds);
    }

    public function testComment()
    {
        $this->exec('git tag 1.0.0');
        $this->workspace()->put('foobar1', '');
        $this->exec('git add foobar1');
        $this->exec('git commit -m "Hello World"');

        $message = wait($this->git->message(
            $this->workspace()->path('/'),
            wait($this->git->headId($this->workspace()->path('/')))
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

    private function listTagNames(): array
    {
        return wait($this->git->listTags($this->workspace()->path('/')))->names();
    }
}
