<?php

namespace Maestro\Model\Job;

interface Job
{
    public function handler(): string;
}