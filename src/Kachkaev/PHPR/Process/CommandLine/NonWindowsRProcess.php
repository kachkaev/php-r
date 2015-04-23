<?php

namespace Kachkaev\PHPR\Process\CommandLine;

class NonWindowsRProcess extends AbstractCommandLineRProcess
{
       protected function setErrorPipe()
    {
        $this->errorPipe = true;
    }
    
    protected function getErrorChannel()
    {
        return array("pipe", "w");
    }
    
    protected function getLastError()
    {
        stream_set_blocking($this->pipes[2], 0);
        return fgets($this->pipes[2]);
    }
    
    protected function getCurrentCommandErrorOutput()
    {
        return fread($this->pipes[2], $this->infiniteLength);
    }
}
