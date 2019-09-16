<?php

namespace Maestro\Tests\Unit\Extension\Template\Task;

use Maestro\Extension\Template\Task\TemplateHandler;
use Maestro\Extension\Template\Task\TemplateTask;
use Maestro\Library\Support\Variables\Variables;
use Maestro\Library\Task\Exception\TaskFailed;
use Maestro\Library\Task\Test\HandlerTester;
use Maestro\Library\Workspace\Workspace;
use Maestro\Tests\IntegrationTestCase;

class TemplateHandlerTest extends IntegrationTestCase
{
    /**
     * @var Workspace
     */
    private $packageWorkspace;
    /**
     * @var TemplateHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->packageWorkspace = new Workspace($this->workspace()->path('/'), 'test');
        $this->handler = $this->container()->get(TemplateHandler::class);
    }

    public function testRendersTemplate()
    {
        $this->workspace()->put(
            'template.twig',
            <<<'EOT'
Hello, {{ name }}.
EOT
        );

        HandlerTester::create($this->handler)->handle(TemplateTask::class, [
            'path' => 'template.twig',
            'targetPath' => 'GREETINGS',
        ], [
            new Variables([
                'manifest.dir' => $this->workspace()->path('/'),
                'name' => 'Dave',
            ]),
            $this->packageWorkspace,
        ]);

        $this->assertFileExists($this->workspace()->path('GREETINGS'));
        $this->assertStringContainsString('Hello, Dave', file_get_contents($this->workspace()->path('GREETINGS')));
    }

    public function testInvalidTemplate()
    {
        $this->workspace()->put(
            'template_2.twig',
            <<<'EOT'
Hello, Dave. {% endif %} {% endblock %} {{asd}}
EOT
        );

        try {
            HandlerTester::create($this->handler)->handle(TemplateTask::class, [
                'path' => 'template_2.twig',
                'targetPath' => 'GREETINGS',
            ], [
                new Variables([
                    'manifest.dir' => $this->workspace()->path('/'),
                ]),
                $this->packageWorkspace,
            ]);
            $this->fail('No exception thrown');
        } catch (TaskFailed $failed) {
            $this->addToAssertionCount(1);
        }
    }

    public function testCreatesNonExistingDirectory()
    {
        $this->workspace()->put(
            'template_3.twig',
            <<<'EOT'
Hello, Dave.
EOT
        );
        HandlerTester::create($this->handler)->handle(TemplateTask::class, [
            'path' => 'template_3.twig',
            'targetPath' => 'foobar/GREETINGS',
        ], [
            new Variables([
                'manifest.dir' => $this->workspace()->path('/'),
            ]),
            $this->packageWorkspace,
        ]);
        $this->assertFileExists($this->workspace()->path('foobar/GREETINGS'));
    }
}
