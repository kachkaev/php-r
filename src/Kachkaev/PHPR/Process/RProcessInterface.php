<?php
namespace Kachkaev\PHPR\Process;

use Kachkaev\PHPR\Exception\RProcessException;
use Kachkaev\PHPR\Exception\RErrorException;

/**
 * RProcessInterface is used in wrappers for a synchronous R interpreter process
 * 
 * Input commands are sent using write().
 * 
 * It is possible to obtain back the input, the output, the list of errors
 * and the overall result (input + output + errors).
 * 
 * Obtaining can be done for all data (since the launch of the process)
 * or only for the last write().
 * 
 * 
 * @author  "Alexander Kachkaev <alexander@kachkaev.ru>"
 *
 */
interface RProcessInterface
{
    /**
     * Starts the R process and also resets errors, input and output
     * 
     * @throws RProcessException if the process is running
     */
    public function start();

    /**
     * Stops the R process
     * 
     * @throws RProcessException if the process is not running
     */
    public function stop();

    /**
     * Restarts the R process (stops and starts it).
     * 
     * @throws RProcessException if the process is not running
     */
    public function restart();
    
    /**
     * Checks if the R process is running
     * @return boolean true if the process has been started, but not stopped; false otherwise
     */
    public function isRunning();

    /**
     * Writes lines of commands to R interpreter
     * 
     * @param string $rInput a multi-line string with commands to execute (no trailing EOL symbol is needed)
     * @return integer the number of errors during the execution (same as getLastWriteErrorCount())
     *
     * @throws RProcessException if the given input does not form a complete 
     *                 command (e.g. "1 + ("), which makes R waiting
     *                 for the rest of a multi-line command.
     *                 Such case is fatal; the process stops.
     */
    public function write($rInput);

    /**
     * Returns all input to the R interpreter
     * 
     * @param boolean $asArray if set to true, an array of strings is returned instead of a single string
     *                 (the input is split by commands)
     * @return string|array all input to R
     */
    public function getAllInput($asArray = false);

    /**
     * Returns all output from the R interpreter
     * 
     * @param boolean $asArray if set to true, an array of strings is returned instead of a single string
     *                 (the output is split by input commands)
     * @return string|array all output from R
     */
    public function getAllOutput($asArray = false);

    /**
     * Returns all input, output and errors (as text or array, depending on $asArray parameter)
     * 
     * As text:
     * --------
     * > 2*2
     * [1] 4
     * > 2*(
     * + 1+1)
     * [1] 4
     * >            <- empty lines are not followed by lines with output (no ">")
     * >
     * >
     * > a*2
     * Error:object 'a' not found
     *
     * 
     * As array:
     * ---------
     * ['input1', 'output1', null]
     * ['input2', null, 'error2']
     * 
     * 0th element: always a string
     * 1st and 2nd elements: a string or null
     * 
     * @param boolean $asArray if set to true, the result is returned as an array
     * @return string|array result of R execution
     */
    public function getAllResult($asArray = false);

    /**
     * Returns the most recent input to the R interpreter (since the last call of write() method)
     * 
     * @param boolean $asArray if set to true, an array of strings is returned instead of a single string
     *                 (the input is split by commands)
     * @return string|array all input to R
     */
    public function getLastWriteInput($asArray = false);

    /**
     * Returns the most recent output from the R interpreter (since the last call of write() method) 
     * 
     * @param boolean $asArray if set to true, an array of strings is returned instead of a single string
     *                 (the output is split by input commands)
     * @return string|array all output from R
     */
    public function getLastWriteOutput($asArray = false);

    /**
     * Returns the most recent input, output and errors (since the last call of write() method)
     * 
     * For details on format see getAllResult()
     *
     * @param boolean $asArray if set to true, the result is returned as an array
     * @return string|array result of R execution
     */
    public function getLastWriteResult($asArray = false);
    
    /**
     * Determines if there were errors since the last call of start() method
     * 
     * @return boolean true if there were errors since the last call of start() method, false otherwise 
     */
    public function hasErrors();

    /**
     * Gets the number of errors that occurred since the last call of start() method
     * 
     * @return integer
     */
    public function getErrorCount();

    /**
     * Gets the array of errors (elements are of type RError) since the last call of start() method
     * 
     * @return array
     */
    public function getErrors();

    /**
     * Determines if there were errors since the last call of write() method
     * 
     * @return boolean true if there were errors since the last call of write() method, false otherwise 
     */
    public function hasLastWriteErrors();

    /**
     * Gets the number of errors that occurred since the last call of write() method
     * 
     * @return integer
     */
    public function getLastWriteErrorCount();

    /**
     * Gets the array of errors (elements are of type RError)
     * that occurred since the last call of write() method 
     * 
     * @return array
     */
    public function getLastWriteErrors();
    
    /**
     * Check if R process is currently sensitive to errors
     * (throws RErrorsException when write() is called)
     */
    public function isErrorSensitive();
    
    /**
     * Sets sensitivity to R errors
     * If enabled, write() throws RErrorsException if they occur
     * Otherwise, the errors are just logged and are accessible via getLastWriteErrors()
     * 
     * @param bool $trueOrFalse
     */
    public function setErrorSensitive($trueOrFalse);
}
