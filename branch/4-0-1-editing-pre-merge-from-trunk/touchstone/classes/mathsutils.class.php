<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Utility class for maths related functionality
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
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
    if ($min == 'ERROR' or $max == 'ERROR') return 'ERROR';
    if ($decimals > 0) {
      $min = $min * (10 * $decimals);
      $max = $max * (10 * $decimals);
      $increment = $increment * (10 * $decimals);
    }
    if ($increment == 1) {
      $gen_no = rand($min, $max);
    } else {
      $new_max = ($max - $min) / $increment;
      $gen_no = rand(0, $new_max);
      $gen_no *= $increment;
      $gen_no += $min;
    }
    if ($decimals > 0) $gen_no = number_format(($gen_no / (10 * $decimals)), $decimals, '.', '');
    return $gen_no;
  }
}


?>