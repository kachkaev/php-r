<?php
namespace Kachkaev\PHPR;

use Kachkaev\PHPR\Engine\REngineInterface;
use Kachkaev\PHPR\Process\RProcessInterface;

class RCore
{
    private $rEngine;
    
    public function __construct(REngineInterface $rEngine)
    {
        $this->rEngine = $rEngine;
    }
    
    public function run($rCode, $resultAsArray = false, $isErrorSensitive = false)
    {
        return $this->rEngine->run($rCode, $resultAsArray, $isErrorSensitive);
    }
    
    public function createInteractiveProcess($isErrorSensitive = false)
    {
        return $this->rEngine->createInteractiveProcess($isErrorSensitive);
    }
}
