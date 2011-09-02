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

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
function display_error($error_title, $error_description, $headers = true, $stop_execution = true) {
  // $error_title - heading which is displayed
  // $error_description - discription of the error together with any information that the user can
  // use to avoid it happening again
  // $eaders - if true output HTML header code
  // $stop_execution - if true exit the script

  global $mysqli, $support_email;
  
  if ($headers == false) {
    echo "<html>\n<head>\n<title>Error</title>\n</head>\n<body style=\"font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black\">\n";
  }
?>
<table border="0" cellpadding="5" cellspacing="0" style="font-size:100%">
<tr><td rowspan="2"><img src="/touchstone/artwork/bomb.png" width="64" height="64" alt="bomb" /></td><td style="font-size:160%; font-weight:bold; color:#C00000"><?php echo $error_title; ?></td></tr>
<tr><td><div><?php echo $error_description; ?></div><br /><div style="color:#808080; font-size:90%">If you repeatedly get this error please contact: <a href="mailto:<?php echo $support_email; ?>" style="color:blue"><?php echo $support_email; ?></a></div></td></tr>
</table>

<?php
  if ($headers == false) {
    echo "</body>\n</html>\n";
  }
  if ($stop_execution == true) {
    $mysqli->close();
    exit;
  }
}

function uploadError($errCode) {
  $engDescription = 'Error - ';
  
  switch ($errCode) {
    case 0:
      $engDescription = 'No problem, the file is uploaded.';
      break;
    case 1:
      $engDescription = 'The uploaded file was bigger then  upload_max_filesize in php.ini.';
      break;
    case 2:
      $engDescription = 'The uploaded file was bigger then MAX_FILE_SIZE in html-form.';
      break;
    case 3:
      $engDescription = 'File partialy uploaded.';
      break;
    case 4:
      $engDescription = 'No file was uploaded.';
      break;
    case 6:
      $engDescription = 'No temp directory.';
      break;
    default:
      $engDescription = 'Unknown problem.';
      break;
  }
  
  return $engDescription;  
}

function check_var($var_name, $method, $mandatory, $headers, $type = '') {
  // $var_name - name of the variable to check
  // $method - $_GET or $_POST
  // $mandatory - if true then exit if the variable does not exist
  // $headers - if true then output HTML header code
  // $type - unused currently
  
  if ($mandatory) {
    if ($method == 'GET') {
      if (empty($_GET[$var_name])) {
        display_error('Fatal Error',"A mandatory GET variable is missing.",$headers);
      }
    } else {
      if (empty($_POST[$var_name])) {
        display_error('Fatal Error',"A mandatory POST variable is missing.",$headers);
      }
    }
  }
}
?>