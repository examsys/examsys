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
* Utility class for network related functionality
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

Class NetworkUtils {
	/**
	 * Get the IP address or name of the computer from the server headers
   * @return mixed client ip address
	 */
  static function get_client_address() {
    $configObject = Config::get_instance();

    // If don't have cached version look it up
    if (!isset($_SESSION['current_ip'])) {
      if ($configObject->get('cfg_client_lookup') == 'name') {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $tmp_parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
          $tmp_client_ipaddress = gethostbyaddr(trim($tmp_parts[0]));
        } else {
          $tmp_client_ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
      } else {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $tmp_parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
          $tmp_client_ipaddress = trim($tmp_parts[0]);
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
          $tmp_client_ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
          $tmp_client_ipaddress = '127.0.0.1';
        }
      }

      $_SESSION['current_ip'] = $tmp_client_ipaddress;
    }

    return $_SESSION['current_ip'];
  }

  static function get_protocol() {
    if ( (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') or (isset($_SERVER['REQUEST_SCHEME']) and $_SERVER['REQUEST_SCHEME'] == 'https') ) {
      return 'https://';
    } else {
      return 'http://';
    }
  }

  static function check_email_domain($output, $domain) {
    global $email;

    if ($output !== true) {
      $output = (substr($email, (strlen($domain) * -1)) == $domain);
    }
    return $output;
  }

  /**
   * Get the IP address of the web server Rogo is running on.
   *
   * @return string The IP address of the webserver.
   */
  static function get_server_address() {
    if (!empty($_SERVER['SERVER_ADDR'])) {
      // This should work on Apache and most other server.
      return $_SERVER['SERVER_ADDR'];
    } elseif (!empty($_SERVER['LOCAL_ADDR'])) {
      // This will work on IIS when PHP is running as a CGI module.
      return $_SERVER['LOCAL_ADDR'];
    } elseif (function_exists('apache_getenv')) {
      // Fall back on an apache method if $_SERVER does not exsist.
      return apache_getenv('SERVER_ADDR');
    } elseif (function_exists('gethostname')) {
      // A possibly expensive emergency fall back, it will return the IP address of the systems name,
      // which maynot be the same as the web server IP, especially if localhost or 127.0.0.1 is being used.
      return gethostbyname(gethostname());
    } else {
      return '0.0.0.0';
    }
  }
}
?>