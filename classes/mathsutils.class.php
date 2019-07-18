<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Utility class for maths related functionality
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

Class MathsUtils {
  /**
   * Returns the factorial of the passed number
   * @param int $number
   * @return int factorial of the given number
   */
  static function factorial($number) {
    $temp = 1;
    while ($number > 1) $temp *= $number--;
    return $temp;
  }
  
  /**
   * Generate a random number between $min and $max with a specified increment and number of decimal places
   * @param mixed $min
   * @param mixed $max
   * @param mixed $increment
   * @param int $decimals
   * @return mixed Random number based on input parameters
   */
  static function gen_random_no($min, $max, $increment, $decimals) {
    if ($min === 'ERROR' or $max === 'ERROR') return 'ERROR';
		if ($min === $max) {  // Both numbers are identical, simply return.
			return $min;
    }
		if ($decimals > 0) {
      $min = $min * (10 * $decimals);
      $max = $max * (10 * $decimals);
      $increment = $increment * (10 * $decimals);
    }
    if ($increment == 1 or $increment == 0 or $increment == '') {
      if (strpos($min,'var') !== false or strpos($min,'ans') !== false or strpos($max,'var') !== false or strpos($max,'ans') !== false) {
        $gen_no = 0;
      } else {
        $gen_no = rand(intval($min), intval($max));
      }
    } else {
      $new_max = ($max - $min) / $increment;
      $gen_no = rand(0, $new_max);
      $gen_no *= $increment;
      $gen_no += $min;
    }
    if ($decimals > 0) $gen_no = number_format(($gen_no / (10 * $decimals)), $decimals, '.', '');
    return $gen_no;
  }
  
  static function formatNumber($number, $decimals = 2) {
    $number = (string) round($number, $decimals);
    
    $number = round($number, $decimals);
    
    if ($decimals > 0) {
      $strlength = strlen($number);
      $decimal_pos = strpos($number, '.');
      
      if ($decimal_pos === false) {
        $number .= '.' . str_repeat('0', $decimals);
      } elseif (($strlength - $decimal_pos  - 1) < $decimals) {
        $target_length = $decimal_pos + $decimals + 1;
        $number = str_pad($number, $target_length, '0');
      }
    }
    
    return $number;
  }
  
  /**
   * Returns the the median of a list of numbers
   * @param array set of numbers you wish to find the median from
   * @return int median of the list
   */
  static function median($arr) {
    sort($arr);
    $count = count($arr); //total numbers in array
    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
    if ($count % 2) { // odd number, middle is the median
      $median = $arr[$middleval];
    } else { // even number, calculate avg of 2 medians
      $low    = $arr[$middleval];
      $high   = $arr[$middleval + 1];
      $median = (($low + $high) / 2);
    }
    
    return $median;
  }
  
  static function mean($arr) {
    $total = array_sum($arr);
    $no = count($arr);
    
    return $total / $no;
  }

  /**
   * Returns a percentile from a list of numbers
   * Not sure what the idea here was as this is not a percentile calculation
   * see https://www.dummies.com/education/math/statistics/how-to-calculate-percentiles-in-statistics/
   * @param array set of numbers to base the percentile on
   * @param float the percentile required
   * @return float the requested percentile
   */
  static function percentile($data, $percentile) {
    $count = count($data);
    if ($count == 0) {
      return 0.0;
    }

    // If any of the array elements are not a number we cannot calculate the percentile so return 0.
    foreach ($data as $idx) {
      if (!is_numeric($idx)) {
        return 0.0;
      }
    }

    if (0 < $percentile and $percentile < 1 ) {
      $p = $percentile;
    } elseif( 1 < $percentile and $percentile <= 100 ) {
      $p = $percentile * .01;
    } else {
      return 0.0;
    }
    $allindex     = ($count-1) * $p;
    $intvalindex  = intval($allindex);
    $floatval     = $allindex - $intvalindex;
    
    rsort($data);
   
    if (!is_float($floatval)){
      $result = $data[$intvalindex];
    } else {
      if ($count > $intvalindex+1) {
        $result = $floatval*($data[$intvalindex+1] - $data[$intvalindex]) + $data[$intvalindex];
      } else {
        $result = $data[$intvalindex];
      }
    }
    
    return $result;
  }
  
}
?>