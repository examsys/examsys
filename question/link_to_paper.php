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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
require '../classes/dateutils.class.php';

check_var('q_id', 'GET', true, false);

if (!isset($_POST['submit'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['linktopaper']; ?></title>
<style>
body {font-family:Arial,sans-serif; margin:0px; background-color:#F1F5FB}
td {font-size:80%}
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
    
    paperTitle = theForm.new_paper.value;
    for (a=0; a<paperTitle.length; a++) {
      char = paperTitle.substr(a,1);
      if (char == '&' || char == '#' || char == '@' || char == '?' || char == '^' || char == '~') {
        alert('A paper name cannot contain any of the following characters:\r      &  #  @  ?  ^  ~');
        return false;
      }
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
    winH -= 170;
    document.getElementById('paperlist').style.height = winH + 'px';
  }
</script>
</head>

<body onload="resizeList();" onresize="resizeList();">
<?php
  echo "<form method=\"post\" name=\"theForm\" onsubmit=\"return checkForm()\" action=\"" . $_SERVER['PHP_SELF'] . "?q_id=" . $_GET['q_id'] . "\">\n";
?>  

  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/link_to_paper.png" width="32" height="32" alt="<?php echo $string['linktopaper']; ?>" /></td><td style="background-color:white; font-size:150%; font-weight:bold; color:#5582D2; border-bottom:1px solid #CCD9EA"><?php echo $string['linktopaper']; ?></td></tr>
  </table>

  <p style="margin:4px; text-align:justify; font-size:70%"><img src="../artwork/small_warning_16.png" width="16" height="16" alt="<?php echo $string['warning']; ?>" border="0" /> <?php echo $string['msg1']; ?></p>
  <p style="margin:4px; text-align:justify; font-size:70%"><img src="../artwork/small_padlock.png" width="16" height="16" alt="<?php echo $string['warning']; ?>" border="0" /> <?php echo $string['msg2']; ?></p>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:4px" id="paperlist">
  <table cellpadding="0" cellspacing="1" border="0">
<?php
  $teamSQL = '';
  foreach ($teams as $team) {
    $teamSQL .= " OR moduleID LIKE '%$team%'";
  }

  $result = $mysqli->prepare("SELECT DISTINCT property_id, paper_title, start_date, end_date, paper_type FROM properties WHERE (paper_ownerID=? $teamSQL) AND deleted IS NULL ORDER BY paper_title");
  $result->bind_param('s', $userID);
  $result->execute();
  $result->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type);
  while ($row = $result->fetch()) {
    if (($paper_type == '2' or $paper_type == '4') and date("Y-m-d H:i:s") > $end_date) {
      echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } elseif ($start_date < date("Y-m-d H:i:s") and $end_date > date("Y-m-d H:i:s")) {
      echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_warning_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\">&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"$property_id\">$paper_title</td></tr>\n";
    }
  }
  $result->close();
  //echo "<tr><td>&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"-new-assessment-paper-\"><input type=\"text\" size=\"40\" name=\"new_paper\" value=\"" . $string['newassessmentpaper'] . "\" /></td></tr>\n</table>\n</div>\n";
  
  
  echo "<tr><td>&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"-new-assessment-paper-\"><input type=\"text\" size=\"40\" name=\"new_paper\" value=\"" . $string['newassessmentpaper'] . "\" />&nbsp;<select name=\"new_module\" style=\"width:220px\">";
  $result = $mysqli->prepare("SELECT moduleid, fullname FROM teams, modules WHERE teams.name=modules.moduleid AND memberID=?");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($moduleid, $fullname);
  while ($result->fetch()) {
    echo "<option value=\"$moduleid\">$moduleid: $fullname</option>";
  }
  $result->close();
  echo "</select></td></tr>\n</table>\n</div>";
  
  
  echo "<div style=\"text-align:center; padding-top:4px;\"><input type=\"submit\" style=\"width:120px\" name=\"submit\" value=\"" . $string['addtopaper'] . "\" />&nbsp;&nbsp;<input type=\"button\" style=\"width:120px\" name=\"cancel\" onclick=\"window.close();\" value=\"" . $string['cancel'] . "\" /></div>\n</form>\n";
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['linktopaper']; ?></title>
</head>
<body style="font-family:Arial,sans-serif;font-size:90%;background-color:EEECDC;text-align:center">
<?php
  $property_id = $_POST['property_id'];
  $q_id = $_GET['q_id'];
  
  if ($property_id == '-new-assessment-paper-') {
    // Check that paper name is not already in use.
    $result = $mysqli->prepare("SELECT property_id FROM properties WHERE paper_title=? LIMIT 1");
    $np_name = $_POST['new_paper'];
    $result->bind_param('s', $np_name);
    $result->execute();  
    $result->store_result();
    $result->bind_result($tmp_id);
    $rows_found = $result->num_rows;
    $result->free_result();
    $result->close();
    if ($rows_found > 0) {
      printf($string['duplicatename'], $_POST['new_paper']);
      echo "<p><input type=\"button\" value=\"" . $string['back'] . "\" style=\"width:100px\" onclick=\"history.back();\" /></p>\n</body>\n</html>\n";
      exit;
    }
    
    // Calculate what the current academic session is.
		$session = DateUtils::get_current_academic_year();
        
    $tmp_paper_title = $_POST['new_paper'];
    
     // Create the new paper.
    $result = $mysqli->prepare("INSERT INTO properties VALUES (NULL,?,'20100101090000','20250101090000','Europe/London',1,'','','white','black','#316AC5','#C00000','1','1','1',40,70,?,'','','',1,'',NULL,NULL,?,0,0,'1','1','1','1','0',?,?,'',NULL,NULL,'0',0,'',NULL,NULL)");
    $result->bind_param('sssis', $tmp_paper_title, $userID, $created, $_POST['new_module'], $session);
    $result->execute();  
    $property_id = $mysqli->insert_id;
    $result->close();
    
    $hash = $property_id . $created . $userID;   // Generate the encrypted name of the paper.

    $result = $mysqli->prepare("UPDATE properties SET deleted=NULL, crypt_name=? WHERE property_id=? LIMIT 1");
    $result->bind_param('si', $hash, $property_id);
    $result->execute();  
    $result->close();

    $display_pos = 1;
    $screen = 1;
  } else {
    // Get the maximum display position for an existing paper.
    $result = $mysqli->prepare("SELECT MAX(display_pos), MAX(screen) FROM papers WHERE paper=?");
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($display_pos, $screen);
    $result->fetch();
    $result->close();
    if ($screen == '') $screen = 1;
    $display_pos++;                     // Add one to put new question right at the end.
  }

  $question_array = array();
  $question_array = explode(',',$q_id);
  foreach ($question_array as $question_part) {
    $result = $mysqli->prepare("INSERT INTO papers VALUES (NULL,?,?,?,?)");
    $result->bind_param('iiii',$property_id,$question_part,$screen,$display_pos);
    $result->execute();  
    $result->close();  
  }

  echo "<p>" . $string['success'] . "</p>\n";
  echo "<p><input type=\"button\" value=\"" . $string['ok'] . "\" style=\"width:100px\" onclick=\"window.close();\" /></p>\n";
}
?>
</body>
</html>