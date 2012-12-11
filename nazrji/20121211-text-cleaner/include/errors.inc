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

require $cfg_web_root . 'lang/' . $language . '/include/errors.inc';

/**
 * Display an error to the screen.
 * @param string $error_title - brief title or summary of the error
 * @param string $error_description - detailed description of what has happed to cause the error
 * @param bool $headers - if true output HTML header code
 * @param bool $stop_execution - if true exit the script
 * @param bool $display_support_email - if true display the support email address (if it is set)
 */
function display_error($error_title, $error_description, $headers = true, $stop_execution = true, $display_support_email = true) {
  global $mysqli, $support_email, $string;
  global $configObject;
  
  if ($headers == false) {
    echo "<html>\n<head>\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n<meta http-equiv=\"content-type\" content=\"text/html;charset={$configObject->get('cfg_page_charset')}\" />\n<title>" . $error_title . "</title>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$configObject->get('cfg_root_path')}/css/body.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$configObject->get('cfg_root_path')}/css/notice.css\" />\n</head>\n<body>\n";
  }
  if ($display_support_email and $support_email != '') {
    $error_description .= '<br /><br /><span style="color:#808080">' . $string['errormsg'] . ' <a href="mailto:' . $support_email . '" style="color:blue">' . $support_email . '</a></span>';
  }
  
  UserNotices::display_notice($error_title, $error_description, $configObject->get('cfg_root_path') . '/artwork/bomb.png', '#C00000');

  if ($headers == false) {
    echo "</body>\n</html>\n";
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
 * @param string $var_name - name of the variable to check
 * @param string $method - $_GET or $_POST
 * @param bool $mandatory - if true then exit if the variable does not exist
 * @param bool $headers - if true then output HTML header code
 * @param string $type - currently unused
 */
function check_var($var_name, $method, $mandatory, $headers, $type = '') {
  global $string;
  
  if ($mandatory) {
    if ($method == 'GET') {
      if (!isset($_GET[$var_name]) or $_GET[$var_name] === '' ) {
        display_error($string['fatalerrormsg0'], $string['fatalerrormsg1'], $headers);
      }
    } elseif ($method == 'POST') {
      if (!isset($_POST[$var_name]) or $_POST[$var_name] === '' ) {
        display_error($string['fatalerrormsg0'], $string['fatalerrormsg2'], $headers);
      }
    } else {
      if (!isset($_REQUEST[$var_name]) or $_REQUEST[$var_name] === '' ) {
        display_error($string['fatalerrormsg0'], $string['fatalerrormsg3'], $headers);
      }
    }
  }
}
?>