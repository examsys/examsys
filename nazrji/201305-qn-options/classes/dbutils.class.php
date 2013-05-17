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
 * Utility class for database related functionality
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

Class DBUtils {
  /**
   * Get a mysqli database connection and set the character set
   *
   * @static
   *
   * @param string $host Host machine for database connection
   * @param string $user Database username
   * @param string $passwd Password for database user
   * @param string $database Initial schema to use
   * @param string $dbclass Optional class to use, e.g. debugging extension to mysqli
   *
   * @return object
   */
  static function get_mysqli_link($host, $user, $passwd, $database, $charset, $notice, $dbclass = 'mysqli', $port = 3306) {

    @$mysqli = new $dbclass($host, $user, $passwd, $database, $port);
    if ($mysqli->connect_error == '') {
      $mysqli->set_charset($charset);
    } else {
      $notice->display_notice('Database Error', "Unable to connect to database using $dbclass.", '/artwork/db_no_connect.png', '#FF7600');
      exit;
    }

    return $mysqli;
  }
}

?>