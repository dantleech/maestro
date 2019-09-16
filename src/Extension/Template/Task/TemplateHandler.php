<?php

namespace Maestro\Extension\Template\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Extension\Template\EnvironmentFactory;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Variables\Variables;
use Maestro\Library\Task\Exception\TaskFailed;
use Maestro\Library\Workspace\Workspace;
use RuntimeException;
use Twig\Error\Error;

class TemplateHandler
{
    /**
     * @var EnvironmentFactory
     */
    private $factory;

    public function __construct(EnvironmentFactory $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(TemplateTask $task, Variables $variables, Workspace $workspace): Promise
    {
        $twigEnvironment = $this->factory->get(
            $variables->get('manifest.dir')
        );

        try {
            $rendered = $twigEnvironment->render($task->path(), $variables->toArray());
        } catch (Error $error) {
            throw new TaskFailed($error->getMessage());
        }

        $this->writeContents($workspace, $task, $rendered);

        return new Success([]);
    }

    private function writeContents(Workspace $workspace, TemplateTask $task, string $rendered): void
    {
        $targetPath = $workspace->absolutePath($task->targetPath());

        if (!file_exists(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0777, true);
        }

        if (!file_put_contents($targetPath, $rendered)) {
            throw new RuntimeException(sprintf(
                'Could not write file contents to "%s"',
                $targetPath
            ));
        }
    }
}
