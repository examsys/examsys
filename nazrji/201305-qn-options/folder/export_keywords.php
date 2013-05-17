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

require '../include/staff_auth.inc';
require '../include/errors.inc';

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename=keywords.txt');

if ($_GET['module'] != '') {
  // Look up team keywords
  $result = $mysqli->prepare("SELECT id, keyword FROM keywords_user WHERE keyword_type='team' AND userID=? ORDER BY keyword");
  $result->bind_param('i', $_GET['module']);
  $result->execute();
  $result->bind_result($keywordID, $keyword);
  while ($result->fetch()) {
    echo "$keyword\r\n";
  }
  $result->close();
} else {
  // Lookup personal keywords
  $result = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE keyword_type='personal' AND userID=? ORDER BY keyword");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($keyword);
  while ($result->fetch()) {
    echo "$keyword\r\n";
  }
  $result->close();
}

$mysqli->close();
?>