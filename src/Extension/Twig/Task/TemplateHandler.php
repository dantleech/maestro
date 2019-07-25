<?php

namespace Maestro\Extension\Twig\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Extension\Twig\EnvironmentFactory;
use Maestro\Graph\Environment;
use Maestro\Graph\Exception\TaskFailed;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;
use Maestro\Workspace\Workspace;
use RuntimeException;
use Twig\Error\Error;

class TemplateHandler implements TaskHandler
{
    /**
     * @var EnvironmentFactory
     */
    private $factory;

    public function __construct(EnvironmentFactory $factory)
    {
        $this->factory = $factory;
    }

    public function execute(Task $task, Environment $environment): Promise
    {
        assert($task instanceof TemplateTask);
        $manifestDir = $environment->vars()->get('manifest.dir');
        $workspace = $environment->workspace();

        $twigEnvironment = $this->factory->get($manifestDir);

        try {
            $rendered = $twigEnvironment->render($task->path(), $environment->vars()->toArray());
        } catch (Error $error) {
            throw new TaskFailed($error->getMessage());
        }

        $this->writeContents($workspace, $task, $rendered);

        return new Success($environment);
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
