<?php

namespace Maestro\Extension\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class EnvironmentFactory
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function get(array $paths): Environment
    {
        return new Environment(new FilesystemLoader($paths), $this->options);
    }
}
