<?php

namespace Maestro\Extension\Template\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Job\QueueDispatcher\Exception\JobFailure;
use Maestro\Model\Package\Workspace;
use Twig\Environment;

class ApplyTemplateHandler
{
    /**
     * @var ConsoleManager
     */
    private $consoleManager;

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $globalParameters;

    public function __construct(
        ConsoleManager $consoleManager,
        Workspace $workspace,
        Environment $twig,
        array $globalParameters
    ) {
        $this->consoleManager = $consoleManager;
        $this->workspace = $workspace;
        $this->twig = $twig;
        $this->globalParameters = $globalParameters;
    }

    public function __invoke(ApplyTemplate $job): Promise
    {
        $packageWorkspace = $this->workspace->package($job->package());
        $rendered = $this->twig->render($job->from(), $this->buildParameters($job));
        $targetPath = $packageWorkspace->path() . '/' . $job->to();

        $this->consoleManager->stdout($job->package()->consoleId())->writeln(sprintf(
            'Applying template "%s" to "%s"',
            $job->from(),
            $targetPath
        ));

        $this->ensureDirectoryExists($targetPath);

        file_put_contents($targetPath, $rendered);

        return new Success(sprintf('Applied %s', $job->from()));
    }

    private function ensureDirectoryExists(string $targetPath): void
    {
        if (file_exists(dirname($targetPath))) {
            return;
        }
        if (mkdir(dirname($targetPath), 0777, true)) {
            return;
        }

        throw new JobFailure(sprintf(
            'Could not create directory "%s"',
            $targetPath
        ));
    }

    private function buildParameters(ApplyTemplate $job): array
    {
        return [
            'package' => $job->package(),
            'globalParameters' => $this->globalParameters
        ];
    }
}
