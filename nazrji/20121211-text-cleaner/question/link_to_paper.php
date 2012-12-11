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

require '../include/staff_auth.inc';
require '../include/errors.inc';
require_once '../classes/dateutils.class.php';
require_once '../classes/paperutils.class.php';

check_var('q_id', 'GET', true, false);

if (!isset($_POST['submit'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['linktopaper']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {font-size:80%}
  </style>

  <script language="JavaScript">
    function checkForm() {
      checkOption = -1
      for (i=0; i<theForm.property_id.length; i++) {
        if (theForm.property_id[i].checked) {
          checkOption = i;
        }
      }
      if (checkOption == -1) {
        alert("Please select which paper you would like to add the question to.");
        return false;
      }
    }

    function resizeList() {
      var winW = 630, winH = 460;
      if (document.body && document.body.offsetWidth) {
        winW = document.body.offsetWidth;
        winH = document.body.offsetHeight;
      }
      if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
        winW = document.documentElement.offsetWidth;
        winH = document.documentElement.offsetHeight;
      }
      if (window.innerWidth && window.innerHeight) {
        winW = window.innerWidth;
        winH = window.innerHeight;
      }
      winH -= 155;
      document.getElementById('paperlist').style.height = winH + 'px';
    }
  </script>
</head>

<body onload="resizeList();" onresize="resizeList();" class="inline_dialog_body">
<?php
  echo "<form method=\"post\" name=\"theForm\" onsubmit=\"return checkForm()\" action=\"" . $_SERVER['PHP_SELF'] . "?q_id=" . $_GET['q_id'] . "\">\n";
?>  

  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/link_to_paper.png" width="32" height="32" alt="<?php echo $string['linktopaper']; ?>" /></td><td class="dialog_header" style="border-bottom:1px solid #CCD9EA"><?php echo $string['linktopaper']; ?></td></tr>
  </table>

  <p style="margin:4px; text-align:justify; font-size:90%"><img src="../artwork/small_warning_16.png" width="16" height="16" alt="<?php echo $string['warning']; ?>" /> <?php echo $string['msg1']; ?></p>
  <p style="margin:4px; text-align:justify; font-size:90%"><img src="../artwork/small_padlock.png" width="16" height="16" alt="<?php echo $string['warning']; ?>" /> <?php echo $string['msg2']; ?></p>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:4px" id="paperlist">
  <table cellpadding="0" cellspacing="1" border="0">
<?php
  $result = $mysqli->prepare("SELECT DISTINCT properties.property_id, paper_title, start_date, end_date, paper_type FROM properties, properties_modules WHERE properties.property_id = properties_modules.property_id AND (paper_ownerID = ? OR idMod IN ('" . implode("','",array_keys($staff_modules)) . "')) AND deleted IS NULL ORDER BY paper_title");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type);
  while ($result->fetch()) {
    if (($paper_type == '2' or $paper_type == '4') and date("Y-m-d H:i:s") > $end_date) {
      echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } elseif ($start_date < date("Y-m-d H:i:s") and $end_date > date("Y-m-d H:i:s")) {
      echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_warning_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\">&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"$property_id\" id=\"$property_id\"><label for=\"$property_id\">$paper_title</label></td></tr>\n";
    }
  }
  $result->close();
  
  echo "</table>\n</div>";
  
  echo "<div style=\"text-align:center; padding-top:4px;\"><input type=\"submit\" style=\"width:120px\" name=\"submit\" value=\"" . $string['addtopaper'] . "\" />&nbsp;&nbsp;<input type=\"button\" style=\"width:120px\" name=\"cancel\" onclick=\"window.close();\" value=\"" . $string['cancel'] . "\" /></div>\n</form>\n";
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo $string['linktopaper']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
</head>

<body style="font-size:90%;background-color:EEECDC;text-align:center">
<?php
  $property_id = $_POST['property_id'];
  $q_id = $_GET['q_id'];
  
  // Get the maximum display position for an existing paper.
  $result = $mysqli->prepare("SELECT MAX(display_pos), MAX(screen) FROM papers WHERE paper=?");
  $result->bind_param('i', $property_id);
  $result->execute();
  $result->bind_result($display_pos, $screen);
  $result->fetch();
  $result->close();
  if ($screen == '') $screen = 1;
  $display_pos++;                     // Add one to put new question right at the end.

  $q_IDs = explode(',', $q_id);
  for ($i=1; $i<count($q_IDs); $i++) {
    Paper_utils::add_question($property_id, $q_IDs[$i], $screen, $display_pos, $mysqli);

    $display_pos++;    
  }

  echo "<p>" . $string['success'] . "</p>\n";
  echo "<p><input type=\"button\" value=\"" . $string['ok'] . "\" style=\"width:100px\" onclick=\"window.close();\" /></p>\n";
}
$mysqli->close();
?>
</body>
</html>