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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/dbutils.class.php';
require_once $cfg_web_root . 'classes/configobject.class.php';

set_error_handler("customErrorHandler");

set_exception_handler("exceptionHandler");

register_shutdown_function("shutdownHandler");

function shutdownHandler() {
  $isError = false;
  if ($error = error_get_last()) {
    customErrorHandler($error['type'], 'At Shutdown:: ' . $error['message'], $error['file'], $error['line'], array());
  }
}


function exceptionHandler($exception) {

  // these are our templates
  $traceline = "#%s %s(%s): %s(%s)";
  $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

  // alter your trace as you please, here
  $trace = $exception->getTrace();

  /*
  foreach ($trace as $key => $stackPoint) {
    // I'm converting arguments to their type
    // (prevents passwords from ever getting logged as anything other than 'string')
    $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
  }
*/
  // build your tracelines
  $result = array();
  foreach ($trace as $key => $stackPoint) {

    if(!isset($stackPoint['file'])) {
      $stackPoint['file']='UNKNOWN';
    }
    if(!isset($stackPoint['line'])) {
      $stackPoint['line']='UNKNOWN';
    }
    if(!isset($stackPoint['function'])) {
      $stackPoint['function']='UNKNOWN';
    }
    if(!isset($stackPoint['args'])) {
      $stackPoint['args']='UNKNOWN';
    }
    /*$result[] = sprintf(
      $traceline,
      $key,
      $stackPoint['file'],
      $stackPoint['line'],
      $stackPoint['function'],
      implode(', ', $stackPoint['args'])
    );*/
  }
  // trace always ends with {main}
  $result[] = '#' . ++$key . ' {main}';

  // write tracelines into main template
  $msg = sprintf(
    $msg,
    get_class($exception),
    $exception->getMessage(),
    $exception->getFile(),
    $exception->getLine(),
    implode("\n", $result),
    $exception->getFile(),
    $exception->getLine()
  );

  // log or echo as you please
  error_log($msg);
  trigger_error($msg, E_USER_WARNING);
}


function customErrorHandler($errno, $errstr, $errfile, $errline, $context) {
  global $debug_pagestart, $dbclass;

  $configObject = Config::get_instance();
  $notice = UserNotices::get_instance();
  $userObject = UserObject::get_instance();

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
      $error_type = "Warning";
      break;
    case E_USER_WARNING:
      $error_type = 'Application Warning';
      break;
    case E_ERROR:
      $error_type = "Fatal Error";
      break;
    case E_USER_ERROR:
      $error_type = "Application Error";
      break;
    default:
      $error_type = "Unknown";
      break;
  }

  // Errors we do not want to log.
  if ($error_type == 'Warning') {
    if (strpos($errstr, 'mysqli::mysqli()') !== false) {
      return true;
    } elseif (strpos($errstr, 'The device is not ready.') !== false) {
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

    if(strpos($errstr,'customErrorHandler') !== false and strpos($errstr,"Couldn't fetch mysqli") !== false ) {
      return true;
    }

    $backtrace = DebugBackTraceDisplay(debug_backtrace());
    try {
      throw new Exception("New Backtrace Method:", 1);
    }
    catch (Exception $e) {
      $string="Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      $string=$string . nl2br($e->getTraceAsString());
      $backtrace=$string . "\r\n<br>Old Method:\r\n<br>" . $backtrace;
    }
    if (is_null($configObject->get('cfg_db_port'))) {
      $configObject->set('cfg_db_port', 3306);
    }
    // Query may fail if we try to insert while another statement is open.
    // Since we don't have a handle on the original statement, create another DB link
    $mysqli2 = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

    $paperID = null;
    if (isset($_GET['paperID'])) {
      $paperID = $_GET['paperID'];
    } elseif (isset($_POST['paperID'])) {
      $paperID = $_POST['paperID'];
    }

    $post_data = '';
    if (isset($_POST)) {
      foreach ($_POST as $key => $value) {
        if ($key != 'ROGO_PW') {
          if ($post_data == '') {
            $post_data = "$key=$value";
          } else {
            $post_data .= ", $key=$value";
          }
        } else {
          $post_data .= ", $key=<HIDDEN>";
        }
      }
    }
if(isset($context) and !empty($context)) {
  $variables = base64_encode(serialize($context));
}
    $log_error = $mysqli2->prepare("INSERT INTO sys_errors VALUES(NULL, NOW(), ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?)");
    $userid = 0;
    $username = 'PRE LOGIN';
    if (isset($userObject)) {
      $userid = $userObject->get_user_ID();
      $username = $userObject->get_username();
    }
$valid_errors=array('Notice','Warning','Fatal Error','Unknown');
    if(!in_array($error_type,$valid_errors)) {
      $error_type='Unknown';
    }

    global $mysqli;
    if (isset($mysqli)) {
      if (isset($mysqli->error)) {
        if (!($mysqli->error == '' or is_null($mysqli->error))) {
          $errstr = $errstr . "\r\n<br>MySQLi Error:\r\n<br>" . $mysqli->error;
        }
      }
    }

    $log_error->bind_param('issssssssisss', $userid, $username, $error_type, $errstr, $errfile, $errline, $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_METHOD'], $paperID, $post_data, $variables, $backtrace);
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

  foreach ($caller as $key => $called) {
    if ($called['function'] == 'customErrorHandler') {
      $str .= '<br />#' . $key . ' CustomeErrorHandler';
    } else {
      if (!isset($called['line'])) {
        $called['line'] = 'Unknown line';
      }
      if (!isset($called['file'])) {
        $called['file'] = 'Unknown file';
      }
      $str = $str . '<br />#' . $key . ' ' . $called['function'] . ' called at [' . $called['file'] . ':' . $called['line'] . ']';
    }
  }
  $str = $str . '<br />';
  $str = substr($str, 6);

  return $str;
}

?>