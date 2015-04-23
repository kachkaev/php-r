<?php

namespace Kachkaev\PHPR\Process\CommandLine;

class WindowsRProcess extends AbstractCommandLineRProcess
{
    private $errorFile;

    public function __construct($rCommand, $errorFile)
    {
        $this->errorFile = $errorFile;
        file_put_contents($this->errorFile, '');
        parent::__construct($rCommand);
    }
    
    protected function setErrorPipe()
    {
        $this->errorPipe = false;
    }
    
    protected function getErrorChannel()
    {
        return array("file", $this->errorFile, "a");
    }
    
    /**
     * See http://stackoverflow.com/questions/1510141/read-last-line-from-file
     */
    protected function getLastError() 
    {
        $line = '';

        $handle = fopen($this->errorFile, 'r');
        $cursor = -1;

        fseek($handle, $cursor, SEEK_END);
        $char = fgetc($handle);

        /**
         * Trim trailing newline chars of the file
         */
        while ($char === "\n" || $char === "\r") {
            fseek($handle, $cursor--, SEEK_END);
            $char = fgetc($handle);
        }

        /**
         * Read until the start of file or first newline char
         */
        while ($char !== false && $char !== "\n" && $char !== "\r") {
            /**
             * Prepend the new char
             */
            $line = $char . $line;
            fseek($handle, $cursor--, SEEK_END);
            $char = fgetc($handle);
        }

        return $line;
    }
    
    protected function getCurrentCommandErrorOutput()
    {
        return $this->getLastError();
    }
}
