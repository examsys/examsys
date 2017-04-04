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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/sysadmin_auth.inc';    // Only let SysAdmin staff delete pages.
require '../../include/errors.php';

$originalID = check_var('id', 'GET', true, false, true);

$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);
$help_system->delete_page($originalID);

$mysqli->close();
header("location: index.php?id=1");
?>
