<?php
namespace Phpactor\Extension\Maestro\Module\Git\State;

use Phpactor\Extension\Maestro\Model\StateMachine\State;
use Phpactor\Extension\Maestro\Module\Filesystem\State\FolderExists;

class RepositoryInitialized implements State
{
    const NAME = 'git_repository_initialized';

    public function name(): string
    {
        return self::NAME;
    }

    public function execute(): void
    {
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
            FolderExists::NAME
        ];
    }
}
