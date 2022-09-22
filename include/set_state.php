<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * This script saves state information to the database. Normally called via AJAX.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
$prefix = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'];
$page = str_ireplace($prefix, '', $_REQUEST['page']);
$page = str_replace('#', '', $page);
$parts = explode('?', $page);
$page = $parts[0];
$userID = $userObject->get_user_ID();
$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$stateutil->setState($_REQUEST['state_name'], $_REQUEST['content'], $page);
