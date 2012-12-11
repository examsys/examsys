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
 * Class for cleaning text for submission to database (in HTML terms, not to escape it)
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */


class TextCleaner {
  public function clean($string) {
    $string = trim($string);

    if ($string != '' and !is_numeric($string)) {
      $string = $this->strip_empty_tags($string);
      if ($string != '') {
        $string = $this->strip_MSO_tags($string);
      }
    }

    return $string;
  }
  
  private function strip_empty_tags($string) {
    $string = preg_replace('/^<div>&nbsp;<\/div>\s*/', '', $string);
    $string = preg_replace('/^<p>&nbsp;<\/p>\s*/', '', $string);
    $string = preg_replace('/^<div><\/div>\s*/', '', $string);
    $string = preg_replace('/^<p><\/p>\s*/', '', $string);
    $string = preg_replace('/^<br \/>\s*/', '', $string);

    return $string;
  }
  
  private function strip_MSO_tags($string) {
    if (strpos($string, 'mso-') !== false or strpos($string, 'MsoNormal') !== false or strpos($string, '<o:p>') !== false) {
      $allowedTags = '<div><b><i><strong><em><sup><sub><table><tr><td><tbody><th>';
      $string = strip_tags($string, $allowedTags);
    }
    // Further processing.
    $string = str_ireplace('PADDING-TOP: 0cm', '', $string);
    $string = str_ireplace('PADDING-BOTTOM: 0cm', '', $string);
    $string = str_ireplace('BACKGROUND-COLOR: transparent;', '', $string);
    $string = str_ireplace('<o:p></o:p>', '', $string);
    $string = str_ireplace('<strong></strong>', '', $string);
    $string = str_ireplace('COLOR: black', '', $string);

    // Convert strange codes.
    $string = str_ireplace('%u2013', '-', $string);
    $string = str_ireplace('%u2018', "'", $string);
    $string = str_ireplace('%u2019', "'", $string);

    // Some regular expressions
    $string = preg_replace('/FONT-SIZE: [0-9]+pt/','',$string);
    $string = preg_replace('/FONT-FAMILY: [\'a-zA-Z 0-9,]*/','',$string);
    $string = preg_replace('/FONT: [0-9]+pt/','',$string);
    $string = preg_replace('/LINE-HEIGHT: [0-9]+%/','',$string);

    $string = preg_replace('/lang="[\-A-Za-z]*"/', '', $string);
    $string = preg_replace('/mso-[\-A-Za-z]+: [_\. \'#\-A-Za-z0-9,]+/','',$string);

    // Do at end.
    $string = str_ireplace('  ', ' ', $string);
    $string = str_ireplace(' ;', '', $string);
    $string = str_ireplace('style="; ', 'style="', $string);
    $string = str_ireplace('style=""', '', $string);
    $string = str_ireplace('style=" "', '', $string);
    $string = str_ireplace('<span >', '<span>', $string);
    $string = str_ireplace('; ">', '">', $string);
    
    return $string;
  }
}
