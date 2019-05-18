<?php

namespace Maestro\Task;

interface Task
{
    public function handler(): string;
}
