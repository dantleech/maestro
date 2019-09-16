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

    public function get(string $path): Environment
    {
        return new Environment(new FilesystemLoader([
            $path
        ]), $this->options);
    }
}
