<?php

namespace Maestro\Tests\Integration\Extension\Twig\Task;

use Maestro\Extension\Twig\EnvironmentFactory;
use Maestro\Extension\Twig\Task\TemplateHandler;
use Maestro\Extension\Twig\Task\TemplateTask;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Test\TaskHandlerTester;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\Workspace;

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
        $this->handler = new TemplateHandler(
            new EnvironmentFactory([
                'strict_variables' => true,
                'auto_reload' => false,
                'cache' => false,
            ])
        );
    }

    public function testRendersTemplate()
    {
        $this->workspace()->put(
            'template.twig',
            <<<'EOT'
Hello, Dave.
EOT
        );

        TaskHandlerTester::create($this->handler)->handle(TemplateTask::class, [
            'path' => 'template.twig',
            'targetPath' => 'GREETINGS',
        ], [
            'workspace' => $this->packageWorkspace,
            'manifest.dir' => $this->workspace()->path('/'),
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
            TaskHandlerTester::create($this->handler)->handle(TemplateTask::class, [
                'path' => 'template_2.twig',
                'targetPath' => 'GREETINGS',
            ], [
                'workspace' => $this->packageWorkspace,
                'manifest.dir' => $this->workspace()->path('/'),
            ]);
            $this->fail('No exception thrown');
        } catch (TaskFailed $failed) {
            $this->assertNotEmpty($failed->artifacts()->get('error'));
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
        TaskHandlerTester::create($this->handler)->handle(TemplateTask::class, [
            'path' => 'template_3.twig',
            'targetPath' => 'foobar/GREETINGS',
        ], [
            'workspace' => $this->packageWorkspace,
            'manifest.dir' => $this->workspace()->path('/'),
        ]);
        $this->assertFileExists($this->workspace()->path('foobar/GREETINGS'));
    }
}
