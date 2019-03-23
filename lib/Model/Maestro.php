<?php

namespace Phpactor\Extension\Maestro\Model;

use Phpactor\Extension\Maestro\Model\StateMachine\StateMachine;
use Phpactor\Extension\Maestro\Module\System\Initialized;

class Maestro
{
    /**
     * @var StateMachine
     */
    private $stateMachine;

    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;#
    }

    public function run()
    {
        $this->stateMachine->goto(Initialized::NAME);
    }
}
