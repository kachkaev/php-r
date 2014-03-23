<?php
namespace Kachkaev\PHPR\Exception;

use Kachkaev\PHPR\RError;

/**
 * R-language-related exception, consisting of one or many instances
 * of RError. To be thrown by any implementation of RProcessInterface
 * when it is error-sensitive.
 * 
 * @author  "Alexander Kachkaev <alexander@kachkaev.ru>"
 */
class RErrorsException extends RException
{
    private $inputLog;
    private $outputLog;
    private $errors;

    public function __construct(array $inputLog, array $outputLog,
            array $errors)
    {
        foreach ($inputLog as $inputLogElement) {
            if (!is_string($inputLogElement)) {
                throw new \InvalidArgumentException(
                        'Argument $inputLog in constructor of RErrorsException must be an array of strings');
            }
        }

        foreach ($outputLog as $outputLogElement) {
            if (!is_null($outputLogElement) && !is_string($outputLogElement)) {
                throw new \InvalidArgumentException(
                        'Argument $outputLog in constructor of RErrorsException must be an array of strings or nulls');
            }
        }
        if (!count($errors)) {
            throw new \InvalidArgumentException(
                    'Argument $errors in constructor of RErrorsException must contain at least one error');
        }

        foreach ($errors as $error) {
            if (!($error instanceof RError)) {
                throw new \InvalidArgumentException(
                        'Argument $errors in constructor of RErrorsException must be an array of RError');
            }
        }

        $this->inputLog = $inputLog;
        $this->outputLog = $outputLog;
        $this->errors = $errors;

        $errorCount = count($errors);
        $message = $errorCount == 1 ? 'One error occurred when running a chunk of R script: '
                : sprintf(
                        '%d errors occurred when running a chunk of R script. First: ',
                        $errorCount);

        $message .= $errors[0]->getErrorMessage();

        parent::__construct($message, 0, null);
    }

    /**
     * @return array
     */
    public function getInputLog()
    {
        return $this->inputLog;
    }

    /**
     * @return array
     */
    public function getOutputLog()
    {
        return $this->outputLog;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
