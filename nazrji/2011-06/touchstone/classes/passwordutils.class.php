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
* Utility class for user related functions
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

Class PasswordUtils {

  /**
   * This is function gen_password makes a secure password
   *
   * @param string $u username
   * @param string $p password
   * @return string password length 8 including uper lower case and other chars
   *
   */
  static function gen_password() {
    $lower = 'abcdefghijklmnoprrstuvwxyzabcdefghijklmnoprrstuvwxyz';
    $upper = 'ABCDEFGHIJKLMN0PQRSTUVWXYZabcdefghijklmnoprrstuvwxyz';
    $num =   '0123456789012345678901234501234567890123456789012345';
    $special ='!$%?#@!.%?#@!$%?#@!$%?#@!.!$%?#@!.%?#@!$%?#@!.%?#@!$'; 
    $pass = '';
    $chars = array($lower,$lower,$lower,$special,$num,$num,$upper,$upper);
    for($i = 0; $i < 8; $i++) { 
      $pass .= substr($chars[$i],rand(0,51),1);
    }
    return $pass;
  }

  /**
   * This is function encpw encrpts a password using md5 and a different salt per user. For 
   * storage in the DB
   *
   * @param string $u username
   * @param string $p password
   * @return string encrypted password
   *
   */
  static function encpw($u,$p) {
    $salt = '$1$' . substr(md5($u),0,12) . '$';
    return crypt($p,$salt);
  }

}
?>