<?php
namespace Kachkaev\PHPR\Exception;

/**
 * To be thrown by an instance of RProcess when a critical
 * error occurs (the process can not keep running) or
 * when the process is misused (e.g. stopped when not running)
 * 
 * @author  "Alexander Kachkaev <alexander@kachkaev.ru>"
 */
class RProcessException extends RException
{

}
