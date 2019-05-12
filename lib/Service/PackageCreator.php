<?php

namespace Maestro\Service;

use Maestro\Model\Package\PackageDefinitionsLoader;

class PackageCreator
{
    /**
     * @var PackageDefinitionsLoader
     */
    private $loader;

    /**
     * @var QueueDispatcher
     */
    private $queueDispatcher;

    /**
     * @var Workspace
     */
    private $workspace;


    public function __construct(
        PackageDefinitionsLoader $loader,
        QueueDispatcher $queueDispatcher,
        Workspace $workspace
    )
    {
        $this->loader = $loader;
        $this->queueDispatcher = $queueDispatcher;
        $this->workspace = $workspace;
    }

    public function create(string $name, string $prototype)
    {
        $definition = $this->loader->load([
            'name' => $name,
            'prototype' => $prototype
        ]);
    }
}
