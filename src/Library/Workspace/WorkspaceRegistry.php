<?php

namespace Maestro\Library\Workspace;

use Maestro\Library\Workspace\Exception\WorkspaceAlreadyRegistred;
use Maestro\Library\Workspace\Exception\WorkspaceNotFound;

class WorkspaceRegistry
{
    /**
     * @var array
     */
    private $workspace = [];

    public function __construct(Workspace ...$workspaces)
    {
        foreach ($workspaces as $workspace) {
            $this->register($workspace);
        }
    }

    public function register(Workspace $workspace)
    {
        if (isset($this->workspace[$workspace->name()])) {
            throw new WorkspaceAlreadyRegistred(sprintf(
                'Workspace "%s" is already registered',
                $workspace->name()
            ));
        }

        $this->workspace[$workspace->name()] = $workspace;
    }

    public function get(string $string): Workspace
    {
        if (!isset($this->workspace[$string])) {
            throw new WorkspaceNotFound(sprintf(
                'Workspace "%s" not found, known workspaces "%s"',
                $string, implode('", "', array_keys($this->workspace))
            ));
        }

        return $this->workspace[$string];
    }
}
