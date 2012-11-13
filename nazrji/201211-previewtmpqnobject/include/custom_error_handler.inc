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
    if (strpos($errstr,'The device is not ready.') !== false) {
      return true;
    } elseif (strpos($errstr,'Access is denied.') !== false) {
      return true;
    } elseif (strpos($errstr,'ldap_bind()') !== false) {
      return true;
    } elseif (strpos($errstr,'ldap_search()') !== false) {
      return true;
    } elseif (strpos($errstr,'simplexml_load_string()') !== false) {
      //QTI imports with invalid XML cause lots of warnings in the error logs. Filter them out.
      return false;  
    }
  }

  if ($error_type != 'Notice4') {  // Record errors that are not notices.

 //   $output_buffer=ob_get_contents();
   // ob_clean();

//    $variables=CaptureVarDump($context);
//$variables=var_export($context,TRUE);

    //$variables=dump($context);

    $variables=base64_encode(serialize($context));

    $backtrace=DebugBackTraceDisplay(debug_backtrace());

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

//    echo $output_buffer;
  }

  return false;
}

function error_disp($message,$level=E_USER_WARNING) {

  $str=DebugBackTraceDisplay(debug_backtrace());

  trigger_error($message  . $str, $level);
}

function DebugBackTraceDisplay($obj) {
  $str='';
  $caller=$obj;
  //array_shift($caller);
  foreach($caller as $key=>$called) {
    if($called['function']=='customErrorHandler') {
      $str .= '<br>#' . $key . ' CustomeErrorHandler';
    } else {
      $str=$str . '<br>#' . $key . ' ' . $called['function'] . ' called at [' . $called['file'] . ':' . $called['line'] . ']';
    }
  }
  $str=$str . '<br>';
  return $str;
}

function backTrace($context=array())
{
  // Get a backtrace of the function calls
  $trace = debug_backtrace( );

  $calls = "\nBacktrace:";

  // Start at 2 -- ignore this function (0) and the customHandler( ) (1)
  for($x=2; $x < count($trace); $x++)
  {
    $callNo = $x - 2;
    $calls .= "\n  {$callNo}: {$trace[$x]["function"]} ";
    $calls .= "(line {$trace[$x]["line"]} in {$trace[$x]["file"]})";
  }

  $calls .= "\nVariables in {$trace[2]["function"]} ( ):";

  // Use the $context to get variable information for the function
  // with the error
 // var_dump($context);
/*  foreach($context as $name => $value)
  {
    if (!empty($value))
      $calls .= "\n  {$name} is {$value}";
    else
      $calls .= "\n  {$name} is NULL";
  }
*/
  return ($calls);
}

function CaptureVarDump($var) {
  //$capture_buffer=print_r($var,TRUE);
  //return '<pre>' . htmlentities($capture_buffer) . '</pre>';

  ini_set('xdebug.var_display_max_children','-1');
  ini_set('xdebug.var_display_max_data','-1');
  ini_set('xdebug.var_display_max_depth','-1');

  //ini_set('xdebug.collect_params','4');


  ob_clean();
  var_dump($var);
  $capture_buffer=ob_get_contents();
  ob_clean();
  return $capture_buffer;

}


function dump(&$var, $info = FALSE)
{
  $str='';
  $scope = false;
  $prefix = 'unique';
  $suffix = 'value';

  if($scope) $vals = $scope;
  else $vals = $GLOBALS;

  $old = $var;
  $var = $new = $prefix.rand().$suffix; $vname = FALSE;
  foreach($vals as $key => $val) if($val === $new) $vname = $key;
  $var = $old;

  $str .= "<pre style='margin: 0px 0px 10px 0px; display: block; background: white; color: black; font-family: Verdana; border: 1px solid #cccccc; padding: 5px; font-size: 10px; line-height: 13px;'>";
  if($info != FALSE) echo "<b style='color: red;'>$info:</b><br>";
  $str .= do_dump($var, '$'.$vname);
  $str .= "</pre>";

  return $str;

}

////////////////////////////////////////////////////////
// Function:         do_dump
// Inspired from:     PHP.net Contributions
// Description: Better GI than print_r or var_dump

function do_dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL)
{

  $str='';
  $do_dump_indent = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp; ";
  $reference = $reference.$var_name;
  $keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';

  if (is_array($var) && isset($var[$keyvar]))
  {
    $real_var = &$var[$keyvar];
    $real_name = &$var[$keyname];
    $type = ucfirst(gettype($real_var));
    $str .= "$indent$var_name <span style='color:#a2a2a2'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
  }
  else
  {
    $var = array($keyvar => $var, $keyname => $reference);
    $avar = &$var[$keyvar];

    $type = ucfirst(gettype($avar));
    if($type == "String") $type_color = "<span style='color:green'>";
    elseif($type == "Integer") $type_color = "<span style='color:red'>";
    elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
    elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
    elseif($type == "NULL") $type_color = "<span style='color:black'>";

    if(is_array($avar))
    {
      $count = count($avar);
      $str .= "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#a2a2a2'>$type ($count)</span><br>$indent(<br>";
      $keys = array_keys($avar);
      foreach($keys as $name)
      {
        if($name != 'GLOBALSxxxxxxx') {
           $value = &$avar[$name];
          $str .= do_dump($value, "['$name']", $indent.$do_dump_indent, $reference);
        }

      }
      $str .= "$indent)<br>";
    }
    elseif(is_object($avar))
    {
      $str .= "$indent$var_name <span style='color:#a2a2a2'>$type</span><br>$indent(<br>";
      foreach($avar as $name=>$value) $str .= do_dump($value, "$name", $indent.$do_dump_indent, $reference);
      $str .= "$indent)<br>";
    }
    elseif(is_int($avar)) $str .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
    elseif(is_string($avar)) $str .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color\"" . htmlentities($avar) . "\"</span><br>";
    elseif(is_float($avar)) $str .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
    elseif(is_bool($avar)) $str .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
    elseif(is_null($avar)) $str .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
    else $str .=  "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $avar<br>";

    $var = $var[$keyvar];
  }

  return $str;
}

?>