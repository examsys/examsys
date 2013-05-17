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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

function is_demo($userObj) {
  if ($userObj->has_role('Demo')) {
    $demo = true;
  } else {
    $demo = false;
  }
}

/**
* This is function demo_replace takes a string and obscures it. Useful for demonstrating Rogo
* at conferences and other meetings. 
* 
* @param string $text the string to be obscured
* @param string $demo_on whether demo mode is on or off
* @return the string obscured as 'Abcde' for as long as the original string was
*
*/
function demo_replace($text, $demo_on, $capitalise=true) {
  if ($demo_on == true) {
    $new_text = '';
    if ($capitalise) {
      $upper_flag = true;
    } else {
      $upper_flag = false;
    }
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
  } else {
    return $text;
  }
}

/**
* This is function demo_replace_number takes a number and obscures it. Useful for
* demonstrating Rogo at conferences and other meetings. 
* 
* @param integer $number the number to be obscured
* @param string $demo_on whether demo mode is on or off
* @return the number obscured as '12345678'
*
*/
function demo_replace_number($number, $demo_on) {
  if ($demo_on == true) {
    return '12345678';
  } else {
    return $number;
  }
}

/**
* This is function demo_replace_username takes a username and obscures by replacing characters with hashes.
* Useful for demonstrating Rogo at conferences and other meetings. 
* 
* @param string $username the username to be obscured
* @param string $demo_on whether demo mode is on or off
* @return the string obscured as a number of hashes
*
*/
function demo_replace_username($username, $demo_on) {
  if ($demo_on == true) {
    $split_username = explode('@',$username);
    $username = '#######';
    
    if (count($split_username) > 1) $username .= '@' . $split_username[1];
  }
  return $username;
}

/**
* This is function demo_replace_name returns generic names so that real ones can be hidden.
* Useful for demonstrating Rogo at conferences and other meetings. 
* 
* @param string $no the array element number to select for the name
* @return the string selected name is returned for display
*
*/
function demo_replace_name($no) {
  $names = array('Bloggs, J. Dr', 'Plinge, W. Dr', 'Frost, J. Mr', 'Doe, J. Dr', 'Smith, J. Dr', 'Nordmann, O. Dr', 'Jobs, S. Mr', 'Shmoe, J. Dr', 'Atkins, T. Mr', 'Bloggs, F. Mr', 'Gates, B. Mr', 'Berners-Lee, T. Mr', 'Andreessen, M. Mr', 'Ellison, L. Mr', 'Bush, V. Prof', 'Gosling, J. Mr', 'Torvalds, L. Mr', 'Clark, A. Mr');
  
  if (isset($names[$no])) {
    $selected = $names[$no];
  } else {
    $selected = 'XXXXX, X, Dr';
  }
  
  return $selected;
}
?>