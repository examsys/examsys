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
* Utility class for useful string functions
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/
class StringUtils {
  /**
   * Return true if string $string end with $test
   *
   * From mcrumley on StackOverflow
   * http://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string
   *
   * @static
   * @param $string
   * @param $test
   * @return bool
   */
  public static function ends_with($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, -$testlen) === 0;
  }

  public static function ordinal_suffix($number, $language='en') {
    $suffix = ($number === 0) ? 'N/A' : $number;
    if ($language == 'en') {
      if ($number !== '') {
  	    switch($number) {
          case 0:
            $suffix .= '';
            break;
  	      case 1:
  	        $suffix .= 'st';
  	        break;
  	      case 2:
  	        $suffix .= 'nd';
  	        break;
  	      case 3:
  	        $suffix .= 'rd';
  	        break;
  	      default:
  	        $suffix .= 'th';
  	        break;
  	    }
      }
    }
    return $suffix;
  }

  /**
   * Remove special characters and trim a string
   * @param  string $string Subject string
   * @return string         Cleaned version of the string
   */
  public static function clean_and_trim($string) {
    $searches = array('&nbsp;');
    $replaces = array(' ');

    $string = str_replace($searches, $replaces, $string);

    return trim($string);
  }
}
