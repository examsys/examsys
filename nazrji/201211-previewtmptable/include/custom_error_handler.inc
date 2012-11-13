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
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/dbutils.class.php';

set_error_handler("customErrorHandler");

function customErrorHandler($errno, $errstr, $errfile, $errline, $context) {
  global $mysqli, $DISABLEDuserID, $debug_pagestart, $dbclass, $cfg_db_host, $cfg_db_username, $cfg_db_passwd, $cfg_db_database, $cfg_db_charset;
  global $userObject; // TODO fix this fudge


  if (isset($debug_pagestart)) {
    //we are in a debug session do not log errors to db just display them
    return false;
  }

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
    if (strpos($errstr, 'The device is not ready.') !== false) {
      return true;
    } elseif (strpos($errstr, 'Access is denied.') !== false) {
      return true;
    } elseif (strpos($errstr, 'ldap_bind()') !== false) {
      return true;
    } elseif (strpos($errstr, 'ldap_search()') !== false) {
      return true;
    } elseif (strpos($errstr, 'simplexml_load_string()') !== false) {
      //QTI imports with invalid XML cause lots of warnings in the error logs. Filter them out.
      return false;
    }
  }

  if ($error_type != 'Notice') { // Record errors that are not notices.

    $variables = base64_encode(serialize($context));

    $backtrace = DebugBackTraceDisplay(debug_backtrace());

    // Query may fail if we try to insert while another statement is open.
    // Since we don't have a handle on the original statement, create another DB link
    $mysqli2 = DBUtils::get_mysqli_link($cfg_db_host, $cfg_db_username, $cfg_db_passwd, $cfg_db_database, $cfg_db_charset, $dbclass);

    $paperID = NULL;
    if (isset($_GET['paperID'])) {
      $paperID = $_GET['paperID'];
    } elseif (isset($_POST['paperID'])) {
      $paperID = $_POST['paperID'];
    }

    $post_data = '';
    if (isset($_POST)) {
      foreach ($_POST as $key => $value) {
        if ($post_data == '') {
          $post_data = "$key=$value";
        } else {
          $post_data .= ", $key=$value";
        }
      }
    }

    $log_error = $mysqli2->prepare("INSERT INTO sys_errors VALUES(NULL, NOW(), ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?)");
    $log_error->bind_param('issssssssisss', $userObject->get_user_ID(), $_SERVER['PHP_AUTH_USER'], $error_type, $errstr, $errfile, $errline, $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_METHOD'], $paperID, $post_data, $variables, $backtrace);
    $log_error->execute();
    $log_error->close();
    $mysqli2->close();

  }

  return false;
}

function error_disp($message, $level = E_USER_WARNING) {

  $str = DebugBackTraceDisplay(debug_backtrace());
  trigger_error($message . $str, $level);
}

function DebugBackTraceDisplay($obj) {
  $str = '';
  $caller = $obj;
  //array_shift($caller);
  foreach ($caller as $key => $called) {
    if ($called['function'] == 'customErrorHandler') {
      $str .= '<br />#' . $key . ' CustomeErrorHandler';
    } else {
      $str = $str . '<br />#' . $key . ' ' . $called['function'] . ' called at [' . $called['file'] . ':' . $called['line'] . ']';
    }
  }
  $str = $str . '<br />';
  $str = substr($str, 6);
  return $str;
}

?>