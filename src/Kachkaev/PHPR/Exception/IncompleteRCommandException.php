<?php
namespace Kachkaev\PHPR\Exception;

/**
 * To be thrown by R process when a command
 * written to R input is incomplete, e.g.: "x = 1 + (".
 * Such situation is critical and forces R process to stop.
 * 
 * @author  "Alexander Kachkaev <alexander@kachkaev.ru>"
 */
class IncompleteRCommandException extends RProcessException
{
    public function __construct($command)
    {
        if (!is_string($command)) {
            throw new \InvalidArgumentException(
                    'Argument $command in constructor of IncompleteRCommandException must be a string');
        }

        $this->command = $command;

        $message = 'The last command in the R input is not complete (missing closing bracket, quotation mark, etc.)';
        parent::__construct($message, 0, null);

    }

    public function getCommand()
    {
        return $this->command;
    }
}
