<?php
namespace Kachkaev\PHPR;

/**
 * Helper class used to parse some common R output into
 * sensible PHP variables
 * 
 * @author  "Alexander Kachkaev <alexander@kachkaev.ru>"
 *
 */
class ROutputInterpreter
{
    /**
     * "[1] 42"
     *  ↓
     *  42
     * 
     * @param string $output
     * 
     * @return numeric
     */
    public function singleNumber($output) {
        //TODO check if r output is a valid number
        return (float) substr($output, 4);
    }
    
    //TODO implement more common cases
}
