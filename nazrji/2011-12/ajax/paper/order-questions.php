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
* Re-order questions based on AJAX call from drag and drop list from paper.details.php
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';

if (isset($_GET['paperID']) and $_GET['paperID'] != '' and isset($_GET['link']) and is_array($_GET['link'])) {
  $paper_id = $_GET['paperID'];
  $new_order = process_new($_GET['link']);
  $old_order = array();

  $result = $mysqli->prepare("SELECT p_id, question, screen, display_pos FROM papers WHERE paper=? ORDER BY display_pos;");
  $result->bind_param('i', $paper_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($p_id, $question, $screen, $display_pos);

  while ($result->fetch()) {
      $old_order[$display_pos] = array('screen' => $screen, 'p_id' => $p_id, 'q_id' => $question);
  }

//  echo '<pre>';
//  print_r($new_order);
//  echo '<br /><br />';
//  print_r($old_order);
//  echo '</pre>';

  $screen_inc = array();
  $screen_dec = array();
  $screen_update = array();
  $position_inc = array();
  $position_dec = array();
  $position_update = array();
  for ($index = 1; $index <= count($new_order); $index++) {
    if ($new_order[$index]['screen'] == ($old_order[$index]['screen'] - 1)) {
      $screen_dec[] = $old_order[$index];
    } elseif ($new_order[$index]['screen'] == ($old_order[$index]['screen'] + 1)) {
      $screen_inc[] = $old_order[$index];
    } elseif ($new_order[$index]['screen'] != $old_order[$index]['screen']) {
      $old_order[$index]['new_screen'] = $new_order[$index]['screen'];
      $screen_update[] = $old_order[$index];
    }

    if ($new_order[$index]['new_pos'] == ($index - 1)) {
      $position_dec[] = $old_order[$index];
    } elseif ($new_order[$index]['new_pos'] == ($index + 1)) {
      $position_inc[] = $old_order[$index];
    } elseif ($new_order[$index]['new_pos'] != $index) {
      $old_order[$index]['new_pos'] = $new_order[$index]['new_pos'];
      $position_update[] = $old_order[$index];
    }
  }

  echo '<pre>Screen Dec:';
  print_r($screen_dec);
  echo '<br /><br />Screen Inc:';
  print_r($screen_inc);
  echo '<br /><br />Screen Update:';
  print_r($screen_update);
  echo '<br /><br />Pos Dec:';
  print_r($position_dec);
  echo '<br /><br />Pos Inc:';
  print_r($position_inc);
  echo '<br /><br />Pos Update:';
  print_r($position_update);
  echo '</pre>';

}

function process_new($raw) {
  $new_order = array();
  $screen = 1;
  $new_pos = 1;

  foreach ($raw as $item) {
    if (strpos($item, 'break') !== false) {
      $screen++;
    } else {
      $new_order[$item] = array('screen' => $screen, 'new_pos' => $new_pos);
      $new_pos++;
    }
  }

  return $new_order;
}
