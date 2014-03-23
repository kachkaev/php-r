<?php
namespace Kachkaev\PHPR\Engine;

use Kachkaev\PHPR\Process\CommandLineRProcess;

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
