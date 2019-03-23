<?php

namespace Phpactor\Extension\Maestro\Module\Filesystem\State;

use Phpactor\Extension\Maestro\Model\StateMachine\State;
use Phpactor\Extension\Maestro\Module\System\ConfigLoaded;

class FolderExists implements State
{
    const NAME = 'filesystem.folder_exists';
    const VAR_PACKAGE_WORKSPACE = 'filesystem.package_workspace';

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
            self::VAR_PACKAGE_WORKSPACE,
            $this->workspace->createPackageWorkspace($context->get(ConfigLoaded::VAR_PACKAGE_NAME))
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
        return [
            ConfigLoaded::NAME
        ];
    }
}
