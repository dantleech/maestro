<?php

namespace Maestro\Adapter\Twig\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Console\ConsoleManager;
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

    public function __construct(
        ConsoleManager $consoleManager,
        Workspace $workspace,
        Environment $twig
    )
    {
        $this->consoleManager = $consoleManager;
        $this->workspace = $workspace;
        $this->twig = $twig;
    }

    public function __invoke(ApplyTemplate $job): Promise
    {
        $packageWorkspace = $this->workspace->package($job->package());
        $this->consoleManager->stdout($job->package()->consoleId())->writeln(sprintf('%s', $packageWorkspace->path()));
        $rendered = $this->twig->render($job->name());
        $targetPath = $packageWorkspace->path() . '/' . $job->name();
        file_put_contents($targetPath, $rendered);

        return new Success(sprintf('Applied %s', $job->name()));
    }
}
