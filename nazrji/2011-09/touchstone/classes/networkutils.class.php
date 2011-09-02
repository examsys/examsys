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
* Utility class for network related functionality
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

Class NetworkUtils {
	/**
	 * Get the IP address of the user from the server headers
   * @return mixed client ip address
	 */
  static function get_ipaddress() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $tmp_parts = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
      $tmp_client_ipaddress = trim($tmp_parts[0]);
    } else {
      $tmp_client_ipaddress = $_SERVER['REMOTE_ADDR'];
    }
    return $tmp_client_ipaddress;
  }
}
?>