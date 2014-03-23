<?php
namespace Kachkaev\PHPR\Engine;

use Kachkaev\PHPR\RProcess\CommandLineRProcess;

class CommandLineREngine extends AbstractREngine
{
    private $rCommand;

    public function __construct($rCommand)
    {
        $this->rCommand = $rCommand;
    }

    protected function createProcess()
    {
        return new CommandLineRProcess($this->rCommand);
    }
}
