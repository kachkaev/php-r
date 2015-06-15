<?php
namespace Kachkaev\PHPR;

/**
 * Helper class used to parse some common R output into
 * sensible PHP variables
 * 
 * @author  "Alexander Kachkaev <alexander@kachkaev.ru>"
 *
 */
class ROutputParser
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
    public function singleNumber($output)
    {
        //TODO properly check if r output is a valid number
        return 0 + substr($output, 4);
    }
    
    /**
     * " [1]   100  200  300 ... 1200"
     * "[13]  1300 1400 1500 ..."
     *  ↓
     *  [100, 200, 300 ...]
     * 
     * @param string $output
     * 
     * @return array
     */
    public function numericVector($output)
    {
        //TODO properly check if r output is a valid vector
        $result = array();
        
        foreach (explode("\n", $output) as $row) {
            // Cut off [?] if needed
            If (strpos($row, ']') !== false) {
                $numbersAsStr = substr($row, strpos($row, ']') + 1); 
            } else {
                $numbersAsStr = $row;
            }
            foreach (explode(' ', $numbersAsStr) as $potentialNumber) {
                if ($potentialNumber !== '') {
                    array_push($result, 0 + $potentialNumber);
                }
            }
        }
        
        return $result;
    }
}
