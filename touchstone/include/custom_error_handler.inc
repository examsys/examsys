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

  set_error_handler("customErrorHandler");

  function customErrorHandler($errno, $errstr, $errfile, $errline) {
    global $mysqli, $userID;
          
    switch ($errno) {
      case E_NOTICE:
      case E_USER_NOTICE:
        $error_type = "Notice";
        break;
      case E_WARNING:
      case E_USER_WARNING:
        $error_type = "Warning";
        break;
      case E_ERROR:
      case E_USER_ERROR:
        $error_type = "Fatal Error";
        break;
      default:
        $error_type = "Unknown";
        break;
    }
    
    // Errors we do not want to log.
    if ($error_type == 'Warning') {
      if (strpos($errstr,'The device is not ready.') !== false) {
        return true;
      } elseif (strpos($errstr,'Access is denied.') !== false) {
        return true;
      } elseif (strpos($errstr,'ldap_bind()') !== false) {
        return true;
      }
    }

    if ($error_type != 'Notice') {  // Record errors that are not notices.
      $log_error = $mysqli->prepare("INSERT INTO sys_errors VALUES(NULL, NOW(), ?, ?, ?, ?, ?, 0 , ?, ?, ?)");
      $log_error->bind_param('isssssss', $userID, $error_type, $errstr, $errfile, $errline, $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_METHOD']);
      $log_error->execute();
      $log_error->close();
    }
   
    return false;
  }
?>