<?php

namespace Maestro\Adapter\Amp\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueModifier;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Package\Workspace;

class PackageInitializerQueueModifier implements QueueModifier
{
    /**
     * @var PackageDefinitions
     */
    private $definitions;
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(PackageDefinitions $definitions, Workspace $workspace)
    {
        $this->definitions = $definitions;
        $this->workspace = $workspace;
    }

    public function modify(Queue $queue): Queue
    {
        foreach ($this->definitions as $definition) {
            assert($definition instanceof PackageDefinition);

            $packagePath = $this->workspace->package($definition)->path();

            if (file_exists($packagePath)) {
                continue;
            }

            $queue->prepend(
                new Process($definition, sprintf('git clone %s %s', $definition->repoUrl(), $packagePath))
            );
        }

        return $queue;
    }
}
