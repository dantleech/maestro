<?php

namespace Maestro\Library\Vcs;

interface RepositoryFactory
{
    public function create(string $path): Repository;
}
