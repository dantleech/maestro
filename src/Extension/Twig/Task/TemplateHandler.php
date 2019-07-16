<?php

namespace Maestro\Extension\Twig\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Extension\Twig\EnvironmentFactory;
use Maestro\Node\Artifacts;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\TaskContext;
use Maestro\Node\TaskHandler;
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

    public function __invoke(TemplateTask $task, TaskContext $context): Promise
    {
        $manifestDir = $context->artifacts()->get('manifest.dir');
        $workspace = $context->artifacts()->get('workspace');
        assert($workspace instanceof Workspace);

        $environment = $this->factory->get($manifestDir);

        try {
            $rendered = $environment->render($task->path(), $context->artifacts()->toArray());
        } catch (Error $error) {
            throw new TaskFailed($error->getMessage(), Artifacts::create([
                'error' => $error->getMessage(),
            ]));
        }

        $this->writeContents($workspace, $task, $rendered);

        return new Success(Artifacts::empty());
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
