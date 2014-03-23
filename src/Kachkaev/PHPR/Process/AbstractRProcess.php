<?php
namespace Kachkaev\PHPR\Process;

use Kachkaev\PHPR\Exception\RErrorsException;
use Kachkaev\PHPR\Exception\RProcessException;

abstract class AbstractRProcess implements RProcessInterface
{
    protected $inputLineCount = 0;
    protected $inputLog = array();
    protected $outputLog = array();
    protected $errors = array();
    protected $lastWriteCommandCount = 0;
    protected $lastWriteErrorCount = 0;
    protected $isRunning = false;
    protected $errorSensitive = false;

    protected $cachedAllResultAsString;
    protected $cachedLastWriteResultAsString;
    protected $cachedAllResultAsArray;
    protected $cachedLastWriteResultAsArray;

    protected abstract function doStart();
    protected abstract function doStop();
    protected abstract function doWrite(array $rInputLines);

    public function start()
    {
        $this->mustNotBeRunning();
        $this->inputLineCount = 0;
        $this->inputLog = array();
        $this->outputLog = array();
        $this->errors = array();
        $this->lastWriteCommandCount = 0;
        $this->lastWriteErrorCount = 0;

        $this->doStart();
        $this->isRunning = true;
    }

    public function stop()
    {
        $this->mustBeRunning();
        $this->doStop();
        $this->isRunning = false;
    }

    public function restart()
    {
        $this->stop();
        $this->start();
    }

    public function isRunning()
    {
        return !$this->isRunning;
    }

    public function write($rInput)
    {
        if (!is_string($rInput)) {
            throw new \InvalidArgumentException(
                    sprintf("R input must be a string, %s given",
                            var_export($rInput, true)));
        }

        $this->mustBeRunning();

        $this->lastWriteCommandCount = 0;
        $this->lastWriteErrorCount = 0;

        $cachedAllResultAsString = null;
        $cachedLastWriteResultAsString = null;
        $cachedAllResultAsArray = null;
        $cachedLastWriteResultAsArray = null;

        try {
            $rInputLines = explode("\n", $rInput);
            $this->doWrite($rInputLines);
        } catch (Exception $e) {
            try {
                $this->stop();
            } catch (Exception $e) {
            }
            throw $e;
        }
        
        $errorCount = $this->getLastWriteErrorCount();
        if ($this->errorSensitive && $errorCount) {
            throw new RErrorsException($this->getLastWriteInput(true), $this->getLastWriteOutput(true), $this->getLastWriteErrors());
        };
        
        return $errorCount;
    }

    public function getAllInput($asArray = false)
    {
        return $asArray ? $this->inputLog : implode("\n", $this->inputLog);
    }

    public function getAllOutput($asArray = false)
    {
        return $asArray ? $this->outputLog : implode("\n", $this->outputLog);
    }

    public function getAllResult($asArray = false)
    {
        $commandCount = count($this->inputLog);

        if ($commandCount == 0) {
            return $asArray ? array() : '';
        }
        ;

        if ($asArray) {
            if (!$this->cachedAllResultAsArray) {
                $this->cachedAllResultAsArray = $this
                        ->getResult(true, 0, $commandCount - 1);
            }
            return $this->cachedAllResultAsArray;
        } else {
            if (!$this->cachedAllResultAsString) {
                $this->cachedAllResultAsString = $this
                        ->getResult(false, 0, $commandCount - 1);
            }
            return $this->cachedAllResultAsString;
        }
    }

    public function getLastWriteInput($asArray = false)
    {
        $lastWriteInput = array_slice($this->inputLog,
                -$this->lastWriteCommandCount, $this->lastWriteCommandCount);
        return $asArray ? $lastWriteInput : implode("\n", $lastWriteInput);
    }

    public function getLastWriteOutput($asArray = false)
    {
        $lastWriteOutput = array_slice($this->outputLog,
                -$this->lastWriteCommandCount, $this->lastWriteCommandCount);
        return $asArray ? $lastWriteOutput : implode("\n", $lastWriteOutput);
    }

    public function getLastWriteResult($asArray = false)
    {
        if ($this->lastWriteCommandCount) {
            return $asArray ? array() : '';
        }

        $commandCount = count($this->inputLog);
        if ($asArray) {
            if (!$this->cachedAllResultAsArray) {
                $this->cachedLastWriteResultAsArray = $this
                        ->getResult(true,
                                $commandCount - $this->lastWriteCommandCount,
                                $commandCount - 1);
            }
            return $this->cachedLastWriteResultAsArray;
        } else {
            if (!$this->cachedLastWriteResultAsString) {
                $this->cachedLastWriteResultAsString = $this
                        ->getResult(false,
                                $commandCount - $this->lastWriteCommandCount,
                                $commandCount - 1);
            }
            return $this->cachedLastWriteResultAsString;
        }
    }

    public function hasErrors()
    {
        return count($this->errors) != 0;
    }

    public function getErrorCount()
    {
        return count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasLastWriteErrors()
    {
        return $this->lastWriteErrorCount != 0;
    }

    public function getLastWriteErrorCount()
    {
        return $this->lastWriteErrorCount;
    }

    public function getLastWriteErrors()
    {
        $lastWriteErrors = array_slice($this->errors,
                -$this->lastWriteErrorCount, $this->lastWriteErrorCount);
        return $lastWriteErrors;

    }

    public function isErrorSensitive()
    {
        return $this->errorSensitive;
    }

    public function setErrorSensitive($trueOrFalse)
    {
        if (!is_bool($trueOrFalse)) {
            throw new \InvalidArgumentException(
                    sprintf(
                            'New value of error sensitivity must be boolean, %s given',
                            var_export($trueOrFalse, true)));
        }
        $this->errorSensitive = $trueOrFalse;
    }

    private function mustBeRunning()
    {
        if (!$this->isRunning) {
            throw new RProcessException(
                    'R process is stopped, it must be started');
        }
    }

    private function mustNotBeRunning()
    {
        if ($this->isRunning) {
            throw new RProcessException(
                    'R process has been started, it must be stopped');
        }
    }

    /**
     * @see AbstractRProcess::getAllResult()
     */
    private function getResult($asArray, $commandNumberFrom, $commandNumberTo)
    {
        if (!is_int($commandNumberFrom) || !is_int($commandNumberTo)
                || $commandNumberFrom < 0
                || $commandNumberTo >= count($this->inputLog)
                || $commandNumberFrom > $commandNumberTo) {
            throw new \InvalidArgumentException(
                    sprintf('Wrong command range: %s, %s',
                            var_export($commandNumberFrom, true),
                            var_export($commandNumberTo, true)));
        }

        $errorsByCommandNumbers = array();

        foreach ($this->errors as $error) {
            $n = $error->getCommandNumber();
            if ($n >= $commandNumberFrom && $n <= $commandNumberTo) {
                $errorsByCommandNumbers[$n] = $error;
            }
        }

        $resultAsArray = array();
        for ($n = $commandNumberFrom; $n <= $commandNumberTo; ++$n) {
            $errorMessage = null;
            if (array_key_exists($n, $errorsByCommandNumbers)) {
                $errorMessage = $errorsByCommandNumbers[$n]->getErrorMessage();
            }
            $resultAsArray[] = array($this->inputLog[$n], $this->outputLog[$n],
                    $errorMessage);
        }

        if ($asArray) {
            return $resultAsArray;
        }

        $resultbyCommands = array();

        foreach ($resultAsArray as $resultCommand) {
            $in = '> ' . str_replace("\n", "\n+ ", $resultCommand[0]);
            $out = $resultCommand[2] ? : $resultCommand[1];

            if (strlen($out)) {
                $resultByCommands[] = $in . "\n" . $out;
            } else {
                $resultByCommands[] = $in;
            }
        }

        return implode("\n", $resultByCommands);
    }
}
