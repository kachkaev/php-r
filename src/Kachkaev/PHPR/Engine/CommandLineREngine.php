<?php
namespace Kachkaev\PHPR\Engine;

use Kachkaev\PHPR\Process\CommandLineRProcess;

class CommandLineREngine extends AbstractREngine
{
    private $rCommand;
    
    private $args;

    public function __construct($rCommand, array $args = null)
    {
        $this->rCommand = $rCommand;
        $this->args = $args;
    }

    protected function createProcess()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')  {
            if (empty($this->args['pathToErrorFile'])) {
                $pathToFileError = "'pathToErrorFile' can't be found. " . 
                    'Make sure to include it as an array argument for CommandLineFactory::createCommandLineProcess.';
                throw new \Exception($pathToFileError);
            }
            
            $errorFile = $this->args['pathToErrorFile'];
            if (!is_writable($errorFile)) {
                $notWritableError = $errorFile . ' is not a writeable file. Make sure ' . $errorFile. 
                    ' exists and has the correct permissions.';
                throw new \Exception($notWritableError);
            }
            
            return new \Kachkaev\PHPR\Process\CommandLine\WindowsRProcess($this->rCommand, $errorFile);
        } else {
            return new \Kachkaev\PHPR\Process\CommandLine\NonWindowsRProcess($this->rCommand);
        }
    }
}
