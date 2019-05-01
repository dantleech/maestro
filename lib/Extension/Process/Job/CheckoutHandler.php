<?php

namespace Maestro\Extension\Process\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Package\Workspace;

final class CheckoutHandler
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function __invoke(Checkout $initJob): Promise
    {
        $package = $initJob->packageDefinition();
        $workspace = $this->workspace->package($package);

        if ($initJob->purge()) {
            $workspace->remove();
        }

        $packagePath = $workspace->path();

        if (file_exists($packagePath)) {
            return new Success();
        }

        $initJob->queue()->prepend(
            new Process(
                $this->workspace->path(),
                sprintf(
                    'git clone %s %s',
                    $this->resolveUrl($initJob),
                    $packagePath
                ),
                $package->consoleId()
            )
        );


        return new Success();
    }

    private function resolveUrl(Checkout $initJob): string
    {
        $url = $initJob->url();
        if ($url) {
            return $url;
        }

        return 'git@github.com:'. $initJob->packageDefinition()->name();
    }
}
