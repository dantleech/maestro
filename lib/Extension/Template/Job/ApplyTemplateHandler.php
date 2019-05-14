<?php

namespace Maestro\Extension\Template\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Tty\TtyManager;
use Maestro\Model\Job\Exception\JobFailure;
use Maestro\Model\Package\Workspace;
use Twig\Environment;

class ApplyTemplateHandler
{
    /**
     * @var TtyManager
     */
    private $ttyManager;

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
    private $parameters;

    public function __construct(
        TtyManager $ttyManager,
        Workspace $workspace,
        Environment $twig,
        array $parameters
    ) {
        $this->ttyManager = $ttyManager;
        $this->workspace = $workspace;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }

    public function __invoke(ApplyTemplate $job): Promise
    {
        $packageWorkspace = $this->workspace->package($job->package());
        $rendered = $this->twig->render($job->from(), $this->buildParameters($job));
        $targetPath = $packageWorkspace->path() . '/' . $job->to();

        $this->ttyManager->stdout($job->package()->ttyId())->writeln(sprintf(
            'Applying template "%s" to "%s"',
            $job->from(),
            $targetPath
        ));

        $this->ensureDirectoryExists($targetPath);

        if (false === $job->overwrite() && file_exists($targetPath)) {
            return new Success();
        }

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
            'parameters' => array_merge(
                $this->parameters,
                $job->package()->parameters()
            ),
        ];
    }
}
