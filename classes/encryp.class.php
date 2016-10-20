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
* Oauth package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Encryption helper class.
 * Interfaces with the vendor/bshaffer/oauth2-server-php
 */
class encryp {
       
    /**
     * Encrypt a password that can be de-crypted.
     * 
     * @param $string $password 
     * @return $string encrypted passsword
     */
    public function mcrypt_password($password) {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $enc = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, UserUtils::get_salt(), $password, MCRYPT_MODE_ECB, $iv);
        return trim(base64_encode($enc));
    }
    /**
    * Decrypt the password.
     * 
     * @param string $enc_password encrypted passsword
     * @return string decrypted passsword
     */
    public function mdecrypt_password($encpassword) {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, UserUtils::get_salt(), base64_decode($encpassword), MCRYPT_MODE_ECB, $iv);
        return trim($dec);
    }
    /**
     * This is function encpw encrpts a password using SHA-512 for storage in the DB.
     * MD5 encryption is kept for backwards compatibility.
     *
     * @param string $salt the salt as set in the config.inc.php file
     * @param string $u username
     * @param string $p password
     * @param string $type the level of encryption to use
     * @return string encrypted password
     *
     */
    public function encpw($salt, $u, $p, $type = 'SHA-512') {
      $supportsha = false;
      if (version_compare(PHP_VERSION, '5.3.2') >= 0) {
        $supportsha = true;
      }
      if ($type == 'SHA-512' and $supportsha == true) {
        $full_salt = '$6$' . $salt . '$'; // SHA-512
        $new_password = crypt($p, $full_salt);
        $new_password = '$6$' . substr($new_password, strlen($full_salt));
      } else {
        $full_salt = '$1$' . substr(md5($u), 0, 8) . '$'; // Simple MD5, for barckwards compatibility
        $new_password = crypt($p, $full_salt);
      }

      return $new_password;
    }

    /**
     * This is function gen_password makes a secure password
     *
     * @param int $len Length og generated password
     * @return string password length $len including upper lower case and other chars
     *
     */
    public function gen_password($len = 8) {
      $lower    = 'abcdefghijklmnoprrstuvwxyzabcdefghijklmnoprrstuvwxyz';
      $upper    = 'ABCDEFGHIJKLMN0PQRSTUVWXYZABCDEFGHIJKLMN0PQRSTUVWXYZ';
      $num      = '0123456789012345678901234501234567890123456789012345';
      $special  = '!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-=+_.@~!?!$%^&*-';

      $pass = '';
      $chars = array($lower, $lower, $lower, $special, $num, $num, $upper, $upper);
      for ($i = 0; $i < $len; $i++) {
        if ($i < 7) {
          $pass .= substr($chars[$i], rand(0, 51), 1);
        } else {
          $pass .= substr($chars[rand(2, 6)], rand(0, 51), 1);
        }
      }
      return $pass;
    }
    
}