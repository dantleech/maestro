<?php

namespace Phpactor\Extension\Maestro\Module\Filesystem\State;

use Phpactor\Extension\Maestro\Model\StateMachine\State;

class FolderExists implements State
{
    const NAME = 'filesystem.folder_exists';

    /**
     * @var RepositoryWorkspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function execute(Context $context): void
    {
        $context->set(
            'filesystem.workspace',
            $this->workspace->createPackageWorkspace($context->packageName())
        );
    }

    public function rollback(): void
    {
    }

    public function predicate(): bool
    {
    }

    public function dependsOn(): array
    {
    }
}
