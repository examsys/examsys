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
* Utility class for user related functions
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once ($cfg_web_root . '/config/config.inc.php');

Abstract Class SmsUtils {

  static function GetSmsUtils() {
    global $cfg_sms_api, $cfg_web_root;
    
    
    if (isset($cfg_sms_api) and $cfg_sms_api != '') {
      require_once ($cfg_web_root . "/apis/" . $cfg_sms_api . ".class.php");
      return new $cfg_sms_api();
    }
    
    return false;
  }
  
  abstract protected function getUserData($username);
  
  abstract protected function getModuleEnrolements($moduleID);
  
  abstract protected function getStudentSources();
  
  abstract protected function getModuleSources();
  
}
?>