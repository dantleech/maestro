<?php

namespace Maestro\Extension;

use RuntimeException;

class NamespaceResolver
{
    /**
     * @var string
     */
    private $workingDir;

    public function __construct(string $workingDir = null)
    {
        $this->workingDir = $workingDir ?? $this->getCwd();

        if (empty($this->workingDir)) {
            throw new RuntimeException(
                'The given working directory path was empty'
            );
        }
    }

    public function resolve()
    {
        return sprintf(
            '%s-%s',
            substr(md5($this->workingDir), 0, 10),
            basename($this->workingDir)
        );
    }

    private function getCwd(): string
    {
        $cwd = getcwd();
        if (false === $cwd) {
            throw new RuntimeException('getcwd returned false, could not determine cwd, this should not happen');
        }
        return $cwd;
    }
}
