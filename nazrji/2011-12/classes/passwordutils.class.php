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
* Utility class for password related functions
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
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
  static function gen_password($len = 8) {
    $lower    = 'abcdefghijklmnoprrstuvwxyzabcdefghijklmnoprrstuvwxyz';
    $upper    = 'ABCDEFGHIJKLMN0PQRSTUVWXYZABCDEFGHIJKLMN0PQRSTUVWXYZ';
    $num      = '0123456789012345678901234501234567890123456789012345';
    $special  = '!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-'; 

    $pass = '';
    $chars = array($lower,$lower,$lower,$special,$num,$num,$upper,$upper);
    for($i = 0; $i < $len; $i++) { 
      if($i < 7) {
        $pass .= substr($chars[$i],rand(0,51),1);
      } else {
        $pass .= substr($chars[rand(2,6)],rand(0,51),1);
      }
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