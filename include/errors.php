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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require $cfg_web_root . 'lang/' . $language . '/include/errors.php';

/**
 * Display an error to the screen.
 * @param string $error_title - brief title or summary of the error
 * @param string $error_description - detailed description of what has happed to cause the error
 * @param bool $headers - if true output HTML header code
 * @param bool $stop_execution - if true exit the script
 * @param bool $display_support_email - if true display the support email address (if it is set)
 */
function display_error($error_title, $error_description, $headers = true, $stop_execution = true, $display_support_email = true) {
  global $mysqli, $string, $notice, $configObject;
  
  $support_email = $configObject->get('support_email');
    
  $user = UserObject::get_instance();
  if ($user !== NULL and $user->get_user_ID() > 0) {
    $logger = new Logger($mysqli);
    $logger->record_access_denied($user->get_user_ID(), $error_title, $error_description);  // Record attempt in access denied log against userID.
  } else {
    $logger = new Logger($mysqli);
    $logger->record_access_denied(0, $error_title, $error_description);                     // Record attempt in access denied log, userID set to zero.
  }
  
  if ($headers == false) {
    echo "<html>\n<head>\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n<meta http-equiv=\"content-type\" content=\"text/html;charset={$configObject->get('cfg_page_charset')}\" />\n<title>" . $error_title . "</title>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$configObject->get('cfg_root_path')}/css/body.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$configObject->get('cfg_root_path')}/css/notice.css\" />\n</head>\n<body>\n";
  }
  if ($display_support_email and $support_email != '') {
    $error_description .= '<br /><br /><span style="color:#808080">' . $string['errormsg'] . ' <a href="mailto:' . $support_email . '" style="color:blue">' . $support_email . '</a></span>';
  }
	$notice->display_notice($error_title, $error_description, '/artwork/square_exclamation_48.png', '#C00000', false, false);

  if ($headers == false) {
    echo "\n</body>\n</html>\n";
  }
  if ($stop_execution == true) {
    $mysqli->close();
    exit;
  }
}

function uploadError($errCode) {
  global $string;
  $engDescription = $string['uploaderrormsg0'];
  
  switch ($errCode) {
    case 0:
      $engDescription = $string['uploaderrormsg1'];
      break;
    case 1:
      $engDescription = $string['uploaderrormsg2'];
      break;
    case 2:
      $engDescription = $string['uploaderrormsg3'];
      break;
    case 3:
      $engDescription = $string['uploaderrormsg4'];
      break;
    case 4:
      $engDescription = $string['uploaderrormsg5'];
      break;
    case 6:
      $engDescription = $string['uploaderrormsg6'];
      break;
    default:
      $engDescription = $string['uploaderrormsg7'];
      break;
  }
  
  return $engDescription;  
}

/**
 * Check that a variable exists of not.
 * @param string  $var_name     Name of the variable to check
 * @param string  $method       Name of superglobal ($_GET, $_POST or $_REQUEST (default)) or an array to search
 * @param bool    $mandatory    If true then exit if the variable does not exist
 * @param bool    $headers      If true then output HTML header code
 * @param bool    $return_var   If true return the value of the tested variable back
 * @param int     $type         The type of value that is expected.
 *
 * @return void|mixed If $return_var is true we will return the value if it is present,
 *                    if the variable is not set and $mandatory is false null will be returned.
 *                    If $return_var is false nothing will be returned.
 *                    In all cases if $mandatory is true and the variable is not set then an error
 *                    page will be displayed and the script will stop processing.
 */
function check_var($var_name, $method, $mandatory, $headers, $return_var, $type = param::RAW) {
  global $string;
  
  if (is_array($method)) {
    if (!isset($method[$var_name]) or $method[$var_name] === '' ) {
      if ($mandatory) {
        display_error($string['fatalerrormsg0'], $string['fatalerrorarray'], $headers);
      } else {
        return null;
      }
    }
    if ($return_var) {
      // Ensure that the value is sanitised.
      return param::clean($method[$var_name], $type);
    } else {
      return;
    }
  }

  switch ($method) {
    case 'GET':
      $from = param::FETCH_GET;
      break;
    case 'POST':
      $from = param::FETCH_POST;
      break;
    default:
      $from = param::FETCH_REQUEST;
  }

  if ($mandatory) {
    try {
      $output = param::required($var_name, $type, $from);
    } catch (MissingParameter $e) {
      // Only catch exceptions for missing parameters.
      switch ($method) {
        case 'GET':
          display_error($string['fatalerrormsg0'], $string['fatalerrormsg1'], $headers);
          break;
        case 'POST':
          display_error($string['fatalerrormsg0'], $var_name, $headers);
          break;
        default:
          display_error($string['fatalerrormsg0'], $string['fatalerrormsg3'], $headers);
      }
    }
  } else {
    // The parameter is not required, we may need to return null.
    $output = param::optional($var_name, null, $type, $from);
  }
  // Check if we should return the value.
  if ($return_var) {
    return $output;
  }
  // We should not return anything.
}
  
?>