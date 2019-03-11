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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once __DIR__ . '/include/load_config.php';
$configObject = Config::get_instance();

if ($configObject->get('cfg_session_name') != '') {
  session_name($configObject->get('cfg_session_name'));
} else {
  session_name('RogoAuthentication');
}
$return = session_start();

session_unset();
session_destroy();
session_write_close();
setcookie(session_name(), '', 0, '/');
header('Location: ./');
exit;