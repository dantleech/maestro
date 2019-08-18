<?php

namespace Maestro\Tests\Integration\Extension\Git\Model;

use Maestro\Extension\Git\Model\Git;
use Maestro\Script\ScriptRunner;
use Maestro\Tests\IntegrationTestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Process\Process;

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
            \Amp\Promise\wait($this->git->listTags($this->workspace()->path('/')))
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

        yield 'sorts tags' => [
            [ '1', '3', '2' ],
            [ '1', '2', '3' ],
        ];
    }

    public function testTagsNewTag()
    {
        \Amp\Promise\wait($this->git->tag($this->workspace()->path('/'), '1.0.0'));
        $this->assertEquals(['1.0.0'], \Amp\Promise\wait($this->git->listTags($this->workspace()->path('/'))));
    }

    public function testIgnoresExistingTag()
    {
        $this->exec('git tag 1.0.0');

        \Amp\Promise\wait(
            $this->git->tag($this->workspace()->path('/'), '1.0.0')
        );

        $this->assertEquals(['1.0.0'], \Amp\Promise\wait($this->git->listTags($this->workspace()->path('/'))));
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
