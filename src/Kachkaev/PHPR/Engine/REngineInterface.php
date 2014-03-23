<?php
namespace Kachkaev\PHPR\Engine;

use Kachkaev\PHPR\RProcess\RProcessInterface;

interface REngineInterface
{
    public function run($rCode, $resultAsArray = false,
            $isErrorSensitive = false);

    /**
     * @param $isErrorSensitive bool sets default error handling strategy.
     *             If true, call of $rProcess->write(?) with input resulting
     *             errors causes the process to throw RErrosException.
     *             Otherwise they are accessible via $rProcess->getLastWriteErrors()
     *             The option can always be changed using $rProcess->setErrorSensitive(true/false) 
     *             
     * @return RProcessInterface
     */
    public function createInteractiveProcess($isErrorSensitive = false);
}
