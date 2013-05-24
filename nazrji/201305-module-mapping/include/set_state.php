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
* @author ?
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  $prefix = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'];
  $page = str_ireplace($prefix, '', $_REQUEST['page']);
  $page = str_replace('#', '', $page);

  $parts = explode('?', $page);
  $page = $parts[0];

  $result = $mysqli->prepare("REPLACE INTO state (userID, state_name, content, page) VALUES (?, ?, ?, ?)");
  $result->bind_param('isss', $userObject->get_user_ID(), $_REQUEST['state_name'], $_REQUEST['content'], $page);
  $result->execute();
?>