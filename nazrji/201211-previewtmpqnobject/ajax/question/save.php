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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../../include/staff_auth.inc';
require_once '../../classes/question.class.php';
require_once '../../classes/moduleutils.class.php';
require_once '../../classes/paperutils.class.php';
require_once '../../include/edit.inc';

require_once '../../include/save_question.inc.php';

$mode = 'none';
$critical_error = '';
$paper_id = (isset($_POST['paperID'])) ? $_POST['paperID'] : -1;
$state = array();

$question = get_question($mode, $critical_error, $userObject, $paper_id, $string, $mysqli, 'draft');

if ($critical_error == '') {
  if (populate_question($question, $userObject->get_user_ID(), $mysqli, 'draft')) {
    $errors = save_question($question, $userObject->get_user_ID(), $paper_id, $mode, $string, $state, $mysqli, 'draft');

    if (count($errors) > 0) {
      echo '<li>' . implode('</li><li>', $errors) . '</li>';
      exit;
    } else {
      echo json_encode($question->serialize());
    }
  }
} else {
	echo $critical_error;
  exit;
}