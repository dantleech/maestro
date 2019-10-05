<?php

namespace Maestro\Extension\Template\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Extension\Template\EnvironmentFactory;
use Maestro\Library\Support\ManifestPath;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Support\Variables\Variables;
use Maestro\Library\Task\Exception\TaskFailure;
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

    public function __invoke(
        TemplateTask $task,
        ManifestPath $manifestPath,
        Variables $variables,
        Workspace $workspace,
        Package $package
    ): Promise {
        $paths = [ $manifestPath->directoryPath() ];

        $twigEnvironment = $this->factory->get($paths);

        try {
            $rendered = $twigEnvironment->render(
                $task->path(),
                array_merge([
                    'package' => $package
                ], $variables->toArray())
            );
        } catch (Error $error) {
            throw new TaskFailure($error->getMessage());
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
