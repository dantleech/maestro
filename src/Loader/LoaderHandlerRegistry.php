<?php

namespace Maestro\Loader;

interface LoaderHandlerRegistry
{
    public function getFor(Loader $loader): LoaderHandler;
}
