<?php

namespace Maestro\Extension\Tmux\Model\Command;

use Maestro\Extension\Tmux\Model\TmuxClient;
use Maestro\Util\Cast;
use Maestro\Workspace\Workspace;
use Maestro\Workspace\WorkspaceFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TmuxCommand extends Command
{
    const ARG_WORKSPACE = 'workspace';

    /**
     * @var TmuxClient
     */
    private $client;

    /**
     * @var WorkspaceFactory
     */
    private $workspaceFactory;

    public function __construct(WorkspaceFactory $workspaceFactory, TmuxClient $client)
    {
        parent::__construct();
        $this->client = $client;
        $this->workspaceFactory = $workspaceFactory;
    }

    protected function configure()
    {
        $this->setDescription('Start a new Tmux session for the given workspace name');
        $this->addArgument(self::ARG_WORKSPACE, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workspace = Cast::toString($input->getArgument(self::ARG_WORKSPACE));

        if (!$this->client->isInside()) {
            throw new RuntimeException('You must be in a Tmux session before using this command, try `tmux new-session -Smyproject`');
        }

        if (false === $this->workspaceFactory->listWorkspaces()->has($workspace)) {
            throw new RuntimeException(sprintf(
                'Unknown workspace "%s", known workspaces: "%s"',
                $workspace,
                implode('", "', $this->workspaceFactory->listWorkspaces()->names())
            ));
        }

        $workspace = $this->workspaceFactory->createNamedWorkspace($workspace);

        $this->ensureSessionExists($workspace, $output);

        $output->writeln(sprintf('<info>Switching to session:</info> %s', $workspace->name()));
        $this->client->switchTo($workspace->name());
    }

    private function ensureSessionExists(Workspace $workspace, OutputInterface $output)
    {
        if (!in_array($workspace->name(), $this->client->listSessions())) {
            $output->writeln(sprintf('<info>Creating new session:</info> %s', $workspace->name()));
            $this->client->createSession($workspace->name(), $workspace->absolutePath());
            return;
        }
        $output->writeln(sprintf('<info>Session already exists:</info> %s', $workspace->name()));
    }
}
