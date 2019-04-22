<?php

namespace Maestro\Console\Command;

use Maestro\Model\Job\QueueStatus;
use Maestro\Service\CommandRunner;
use Maestro\Model\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Exec extends Command
{
    const ARG_COMMAND = 'exec';
    const OPTION_RESET = 'reset';


    /**
     * @var CommandRunner
     */
    private $commandRunner;

    public function __construct(CommandRunner $commandRunner)
    {
        parent::__construct();
        $this->commandRunner = $commandRunner;
    }

    protected function configure()
    {
        $this->addArgument(self::ARG_COMMAND, InputArgument::REQUIRED, 'Command to run on repositories');
        $this->addOption(self::OPTION_RESET, null, InputOption::VALUE_NONE, 'Reset the package repositories');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statuses = $this->commandRunner->run(
            (string) $input->getArgument(self::ARG_COMMAND),
            (bool) $input->getOption(self::OPTION_RESET)
        );

        $table = new Table($output);
        $table->setHeaders([
            'id', 'status', 'last chunk'
        ]);

        foreach ($statuses as $status) {
            assert($status instanceof QueueStatus);
            $interval = $status->start->diff($status->end);
            $table->addRow([
                $status->id,
                sprintf(
                    '%ds %s => %s',
                    $interval->s + ($interval->m * 60),
                    $status->success ? '✔' : '✘',
                    $status->code,
                    ),
                substr(str_replace("\n", ' ', trim(preg_replace('{[[:^print:]]}', ' ', $status->message))), 0, 80),
            ]);
        }

        $table->render();
    }
}
