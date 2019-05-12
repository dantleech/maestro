<?php

namespace Maestro\Console\Command;

use Maestro\Console\Util\Cast;
use Maestro\Service\PackageCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageNew extends Command
{
    const ARG_NAME = 'name';
    const ARG_PROTOTYPE = 'prototype';


    /**
     * @var PackageCreator
     */
    private $creator;

    public function __construct(PackageCreator $creator)
    {
        parent::__construct();
        $this->creator = $creator;
    }

    protected function configure()
    {
        $this->addArgument(self::ARG_NAME, InputArgument::REQUIRED);
        $this->addArgument(self::ARG_PROTOTYPE, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->creator->create(
            Cast::toString($input->getArgument(self::ARG_NAME)),
            Cast::toString($input->getArgument(self::ARG_PROTOTYPE))
        );
    }
}
