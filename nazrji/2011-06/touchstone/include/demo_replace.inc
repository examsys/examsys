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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

function demo_replace($text, $demo_on) {
  if ($demo_on == true) {
    $new_text;
    $upper_flag = true;
    for ($i=0; $i<strlen($text); $i++) {
      if (substr($text,$i,1) == ' ') {
        $new_text .= ' ';
        $upper_flag = true;
      } elseif ($upper_flag == true) {
        $new_text .= chr($i + 65);
        $upper_flag = false;
      } else {
        $new_text .= chr($i + 97);
      }
    }
    return $new_text;
    //return str_repeat('~',strlen($text));
  } else {
    return $text;
  }
}

function demo_replace_number($number, $demo_on) {
  if ($demo_on == true) {
    return '12345678';
  } else {
    return $number;
  }
}

function demo_replace_username($username, $demo_on) {
  if ($demo_on == true) {
    $split_username = explode('@',$username);
    if (substr($username,0,3) == 'mzy') {
      $username = 'mzy####';
    } else {
      $username = '#######';
    }
    if (count($split_username) > 1) $username .= '@' . $split_username[1];
  }
  return $username;
}
?>