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
 * @author Simon Wilkinson, Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

set_error_handler("customErrorHandler");

set_exception_handler("exceptionHandler");

register_shutdown_function("shutdownHandler");

function shutdownHandler() {
  $isError = false;
  $configObject = Config::get_instance();
  $erratshut=$configObject->get('errorshutdownhandling');
  if ($error = error_get_last() and $erratshut !== false) {
    customErrorHandler($error['type'], 'At Shutdown:: ' . $error['message'], $error['file'], $error['line'], array());
  }
}


function exceptionHandler($exception) {
  $configObject = Config::get_instance();
  $notice = UserNotices::get_instance();
  $userObject = UserObject::get_instance();

  // these are our templates
  $traceline = "#%s %s(%s): %s(%s)";
  $msg = "Uncaught Exception error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

  // alter your trace as you please, here
  $trace = $exception->getTrace();

  // build your tracelines
  $result = array();
  foreach ($trace as $key => $stackPoint) {

    if (!isset($stackPoint['file'])) {
      $stackPoint['file'] = 'UNKNOWN';
    }
    if (!isset($stackPoint['line'])) {
      $stackPoint['line'] = 'UNKNOWN';
    }
    if (!isset($stackPoint['function'])) {
      $stackPoint['function'] = 'UNKNOWN';
    }
    if (!isset($stackPoint['args'])) {
      $stackPoint['args'] = 'UNKNOWN';
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
        if (is_array($value)) {
          $value = var_export($value, true);
        }
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
  $variables = 'exception handler: therefore unknown';
  $error_type = "Application Error";
  $errstr = $msg;
  $userid = 0;
  $username = 'PRE LOGIN';
  if (isset($userObject)) {
    $userid = $userObject->get_user_ID();
    $username = $userObject->get_username();
  }
  $valid_errors = array('Notice', 'Warning', 'Fatal Error', 'Unknown', 'Application Error');
  if (!in_array($error_type, $valid_errors)) {
    $error_type = 'Unknown';
  }

  global $mysqli;
  if (isset($mysqli)) {
    if (isset($mysqli->error)) {
      if (!($mysqli->error == '' or is_null($mysqli->error))) {
        $errstr = $errstr . "\r\n<br />MySQLi Error:\r\n<br />" . $mysqli->error;
      }
    }
  }

  log_error($userid, $username, $error_type, $errstr, $exception->getFile(), $exception->getLine(), $paperID, $post_data, $variables, $exception->getTraceAsString(), $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_METHOD']);
}


function error_handling($context = null) {
  if (is_null($context) or !isset($context)) {
    // If no array set get currently define variables in this object.
    return 'NOTHING PARSED TO DECODE';
  }

  $configObject = Config::get_instance();
  $context1 = array();

  $errorcontexthandling = $configObject->get('errorcontexthandling');

  if ($errorcontexthandling == 'basic') {
    $context1 = $context;

  } elseif ($errorcontexthandling == 'improved' or is_null($errorcontexthandling)) {

    foreach ($context as $key => $value) {
      if ($key == 'this') {
      } elseif (is_object($value) and method_exists($value, 'error_handling')) {
        $context1[$key] = $value->error_handling();
      } elseif (is_object($value) and get_class($value) === 'mysqli') {
        $context1[$key] = 'MYSQLi Object';
      } elseif (is_object($value) and get_class($value) === 'mysqli_stmt') {
        $context1[$key] = 'MYSQLi statement Object';
      } elseif (is_object($value) and get_class($value) === 'ZipArchive') {
        $context1[$key] = 'ZipArchive';
      } elseif (is_object($value) and get_class($value) === 'SimpleXMLElement') {
        $context1[$key] = 'SimpleXMLElement';				
      } elseif (is_object($value) and get_class($value) === 'XMLWriter') {
        $context1[$key] = 'XMLWriter';
      } elseif (is_object($value) and get_class($value) === 'XMLReader') {
        $context1[$key] = 'XMLReader';  
      } elseif (is_object($value) and (get_class($value) === 'ErrorException' or get_class($value) === 'Exception')) {
        $context1[$key] = 'ErrorException Object';
      } elseif (is_object($value)) {
        $context1[$key] = clone $value;
      } elseif (is_array($value) and $key === 'string' and (!($configObject->get('debug_lang_string') === true))) {
        $context1[$key] = 'Language Abstraction String - hidden by config option';
      } elseif (is_array($value) and ($key === '_POST' or $key === '_REQUEST')) {
        $tmp = array();
        foreach ($value as $key1 => $value1) {
          if ($key != 'ROGO_PW') {
            $tmp[$key1] = $value1;
          } else {
            $tmp[$key1] = '<HIDDEN>';
          }
        }
        $context1[$key] = $tmp;
      } elseif (is_array($value)) {
        // Just use basic error handling as this was causing endless loops when fed back recursively into this function.
        return $context;
      } else {
        $context1[$key] = $value;
      }
    }
  } else  { // eg $errorcontexthandling=='none'
    $context1='Config option errorcontexthandling disabled capturing this';
  }

  return $context1;
}

function customErrorHandler($errno, $errstr, $errfile, $errline, $context) {
  global $debug_pagestart, $mysqli;
	
  $variables = '';
  $configObject = Config::get_instance();
  $notice = UserNotices::get_instance();
  $userObject = UserObject::get_instance();

  $disperrors = $configObject->get('displayerrors');
  $dispallerrors = $configObject->get('displayallerrors');
  $logerrors = $configObject->get('logerrors');

  // Log level.
  if ($disperrors or $logerrors) {
    error_reporting(E_ALL);
  } else {
    error_reporting(0);
  }

  // Display errors.
  if ($disperrors === true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
  } else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
  }

  // Log errors.
  if ($logerrors === true) {
    ini_set('log_errors' , 1);
  } else {
    ini_set('log_errors' , 0);
  }
  if (isset($debug_pagestart)) {
    // We are in a debug session do not log errors to DB just display them.
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
  if ($error_type == 'Warning' and $dispallerrors !== true) {
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


  if ($dispallerrors === true or $error_type != 'Notice') { // Record errors that are not notices.

    if (strpos($errstr,'customErrorHandler') !== false and strpos($errstr,"Couldn't fetch mysqli") !== false ) {
      return true; // DO NOT REMOVE THIS STOPS mysqli error when retreiving mysqli->error in some situations
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
					if (is_array($value)) { 
						$value = var_export($value, true);
					}
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

    if (isset($context) and !empty($context)) {
			$context = error_handling($context);

      try {
        $variables = base64_encode(serialize($context));
      } catch (Exception $e) {
        try {
          $context = array( 'Had a problem with the capture context due to exception:: ' . $e->getMessage(), 'using alternative method var_export to string instead', var_export($context, TRUE) );
          $variables = base64_encode(serialize($context));
        } catch (Exception $e) {
          $context = 'Couldnt capture context on 2nd attempt due to exception:: ' . $e->getMessage();
          $variables = base64_encode(serialize($context));
        }
      }
    }

    $userid = 0;
    $username = 'PRE LOGIN';
    if (isset($userObject)) {
      $userid = $userObject->get_user_ID();
      $username = $userObject->get_username();
    }
		$valid_errors = array('Notice', 'Warning', 'Fatal Error', 'Unknown', 'Application Warning', 'Application Error');
    if (!in_array($error_type, $valid_errors)) {
      $error_type = 'Unknown';
    }

    if (isset($mysqli) and is_object($mysqli)  ) {
      if ($error_type != 'Notice' and isset($mysqli->error)) {
        if (!($mysqli->error == '' or is_null($mysqli->error))) {
          $errstr = $errstr . "\r\n<br />MySQLi Error:\r\n<br />" . $mysqli->error;
        }
      }
    }

    if (!isset($_SERVER['PHP_SELF'])) {
      $_SERVER['PHP_SELF'] = 'NOT RUN FROM WEBSERVER';
    }
    if (!isset($_SERVER['QUERY_STRING'])) {
      $_SERVER['QUERY_STRING'] = 'NOT RUN FROM WEBSERVER';
    }
    if (!isset($_SERVER['REQUEST_METHOD'])) {
      $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    log_error($userid, $username, $error_type, $errstr, $errfile, $errline, $paperID, $post_data, $variables, $backtrace, $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_METHOD']);
  }

  return false;
}

/**
 * Records details about an error in the database.
 * @param int $userid         - ID of the current user.
 * @param int $username       - Username of the current user.
 * @param int $error_type     - Type of the error (e.g. Notice).
 * @param int $errstr         - Description of the error (e.g. 'Undefined variable: property_id').
 * @param int $errfile        - What file had the error.
 * @param int $errline        - Line number where error occurred.
 * @param int $paperID        - If applicable what paper ID is being used.
 * @param int $post_data      - $_POST data.
 * @param int $variables      - Variable data.
 * @param int $backtrace      - Backtrace information.
 * @param int $page           - Page (script) that encounted the error.
 * @param int $querystring    - The query string passed to the script.
 * @param int $requestmethod  - What request method was used.
 */
function log_error($userid, $username, $error_type, $errstr, $errfile, $errline, $paperID = '', $post_data = '', $variables = '', $backtrace = '', $page = null, $querystring = null, $requestmethod = null) {
  if (is_null($backtrace)) {
    $backtrace='';
    $backtrace = DebugBackTraceDisplay(debug_backtrace());
    try {
      throw new Exception("New Backtrace Method:", 1);
    }
    catch (Exception $e) {
      $string = "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      $string = $string . nl2br($e->getTraceAsString());
      $backtrace = $string . "\r\n<br>Old Method:\r\n<br>" . $backtrace;
    }
  }
  if (is_null($post_data)) {
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

  }
  if ($variables != '' and !is_string($variables)) {
    $variables = error_handling($variables);
    try {
      $variables = base64_encode(serialize($variables));
    } catch (Exception $e) {
      try {
        $variables = array( 'Had a problem with the capture context due to exception:: ' . $e->getMessage(), 'using alternative method var_export to string instead', var_export($variables, TRUE) );
        $variables = base64_encode(serialize($variables));
      } catch (Exception $e) {
        $variables = 'Couldnt capture context on 2nd attempt due to exception:: ' . $e->getMessage();
        $variables = base64_encode(serialize($variables));
      }

    }
  }

  if (is_null($page)) {
    if (!isset($_SERVER['PHP_SELF'])) {
      $_SERVER['PHP_SELF'] = 'NOT RUN FROM WEBSERVER';
    }
    $page = $_SERVER['PHP_SELF'];
  }
  if (is_null($querystring)) {
    if(!isset($_SERVER['QUERY_STRING'])) {
      $_SERVER['QUERY_STRING'] = 'NOT RUN FROM WEBSERVER';
    }
    $querystring = $_SERVER['QUERY_STRING'];
  }
  if (is_null($requestmethod)) {
    if (!isset($_SERVER['REQUEST_METHOD'])) {
      $_SERVER['REQUEST_METHOD'] = 'GET';
    }
    $requestmethod = $_SERVER['REQUEST_METHOD'];
  }

  $configObject = Config::get_instance();
  $notice = UserNotices::get_instance();

  // Query may fail if we try to insert while another statement is open.
  // Since we don't have a handle on the original statement, create another DB link
  $mysqli2 = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));
  $log_error = $mysqli2->prepare("INSERT INTO sys_errors VALUES(NULL, NOW(), ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?)");
  $log_error->bind_param('issssssssisss', $userid, $username, $error_type, $errstr, $errfile, $errline, $page, $querystring, $requestmethod, $paperID, $post_data, $variables, $backtrace);
  $log_error->execute();
  $log_error->close();
  $mysqli2->close();

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
