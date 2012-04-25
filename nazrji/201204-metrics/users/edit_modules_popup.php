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
* Edit a students modules
*  
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require_once '../include/admin_auth.inc';
  require_once '../include/errors.inc';
  require_once '../classes/dateutils.class.php';
  
  function drawTabs($current_tab) {
    $html = '<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%"><tr><td style="width:264px"><strong>Modules for ' . $_GET['session'] . ':</strong></td>';
    $tab_array = array('','1st Attempt','Resit 1','Resit 2');
    for ($i=1; $i<=3; $i++) {
      if ($i == $current_tab) {
        $html .= "<td style=\"cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; background-image:url(../artwork/tab_on.gif)\" onclick=\"showTab('list$i')\">" . $tab_array[$i] . "</td>";
      } else {
        $html .= "<td style=\"cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; background-image:url(../artwork/tab_off.gif)\" onclick=\"showTab('list$i')\">" . $tab_array[$i] . "</td>";
      }
    }
    $html .= "</tr></table>\n";
    return $html;
  }

  function list_modules($mod, $id, $student_mod) {
    $old_letter = '';
    
    if ($id == '1') {
      echo "<div style=\"display:block; width:100%\" id=\"list$id\">";
    } else {
      echo "<div style=\"display:none; width:100%\" id=\"list$id\">";
    }
    
    echo drawTabs($id);
    
    if ($id == '1') {
      echo "<div style=\"width:100%; height:660px; overflow-y:scroll; border:1px solid #95AEC8; background-color:white; font-size:90%\" id=\"list$id\">";
    } else {
      echo "<div style=\"width:100%; height:660px; overflow-y:scroll; border:1px solid #95AEC8; background-color:white; font-size:90%\" id=\"list$id\">";
    }
    
    $mod_count = count($mod);
    for ($module_no=0; $module_no<$mod_count; $module_no++) {
      $moduleid = $mod[$module_no]['id'];
      $fullname = $mod[$module_no]['fullname'];
      
      if ($old_letter != strtoupper(substr($moduleid,0,1))) {
        echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>&nbsp;" . strtoupper(substr($moduleid,0,1)) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
        //echo "<div style=\"background-color:white; font-weight:bold\">&nbsp;" . strtoupper(substr($moduleid,0,1)) . "</div>\n<div style=\"background-color:white; padding-top:2px\"><img src=\"../artwork/divider_bar.gif\" width=\"290\" height=\"1\" /></div>\n";
      }

      $match = false;
      foreach ($student_mod as $tmp_module) {
        if ($tmp_module['id'] == $moduleid and $tmp_module['attempt'] == $id) $match = true;
      }
   
      if ($match == true) {
        echo "<div style=\"text-indent:-23px; padding-left:43px; background-color:#B3C8E8\" id=\"divmod" . $id . "_" . $module_no . "\"><input type=\"checkbox\" onclick=\"toggle('divmod" . $id . "_" . $module_no . "')\" name=\"mod" . $id . "_" . $module_no . "\" value=\"" . $moduleid . "\" checked />&nbsp;$moduleid:&nbsp;$fullname</div>\n";
      } else {
        echo "<div style=\"text-indent:-23px; padding-left:43px; background-color:white\" id=\"divmod" . $id . "_" . $module_no . "\"><input type=\"checkbox\" onclick=\"toggle('divmod" . $id . "_" . $module_no . "')\" name=\"mod" . $id . "_" . $module_no . "\" value=\"" . $moduleid . "\" />&nbsp;$moduleid:&nbsp;$fullname</div>\n";
      }
      $old_letter = strtoupper(substr($moduleid,0,1));
    }
    echo "</div>\n</div>\n";
  }
  
  if (isset($_POST['submit'])) {
    for ($attempt=1; $attempt<=3; $attempt++) {
      // Clear the student of all modules.
      $result = $mysqli->prepare("DELETE FROM student_modules WHERE userID=? AND calendar_year=? AND attempt=?");
      $result->bind_param('isi', $_POST['userID'], $_POST['session'], $attempt);
      $result->execute();  
      $result->close();
      
      // Insert a record for each module.
      for ($i=0; $i<$_POST['mod_count']; $i++) {
        if (isset($_POST['mod' . $attempt . '_' . $i]) and $_POST['mod' . $attempt . '_' . $i] != '') {
          $result = $mysqli->prepare("INSERT INTO student_modules VALUES (NULL,?,?,?,?,0)");
          $result->bind_param('issi', $_POST['userID'], $_POST['mod' . $attempt . '_' . $i], $_POST['session'], $attempt);
          $result->execute();
          $result->close();
        }
      }
    }
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $_POST['session']; ?> Modules</title>
  <script type="text/javascript">
    function closeWindow() {
      window.opener.location.href = 'details.php?userID=<?php echo $_POST['userID']; ?>&tab=modules';
      self.close();
    }
  </script>
</head>
<body onload="closeWindow()">
</body>
</html>
<?php
  } else {
  
  if (isset($_GET['session']) and $_GET['session'] != '') {
    $session = $_GET['session'];
  } else {
    $session =  DateUtils::get_current_academic_year();

  }
?>
<html>
<head>
<title><?php echo $session; ?> Modules</title>
<style type="text/css">
  body {font-family:Arial,sans-serif; font-size:90%; background-color:#E3EFFF; color:black; margin:8px 4px 4px 4px}
  td {font-size:90%}
</style>
<script language="JavaScript">
  function toggle(objectID) {
    if (document.getElementById(objectID).style.backgroundColor == 'white') {
      document.getElementById(objectID).style.backgroundColor = '#B3C8E8';
    } else {
      document.getElementById(objectID).style.backgroundColor = 'white';
    }
  }
  
  function showTab(tabID) {
    document.getElementById('list1').style.display = 'none';
    document.getElementById('list2').style.display = 'none';
    document.getElementById('list3').style.display = 'none';
    
    document.getElementById(tabID).style.display = 'block';
  
  }
</script>
</head>
<body>
<form name="teamform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<?php
  // Get existing modules for the user in passed calendar year.
  $student_modules = array();
  $student_mod_count = 0;
  $result = $mysqli->prepare("SELECT moduleid, attempt FROM student_modules WHERE userID=? AND calendar_year=?");
  $result->bind_param('is', $_GET['userID'], $session);
  $result->execute();
  $result->bind_result($moduleid, $attempt);
  while ($row = $result->fetch()) {
    $student_modules[$student_mod_count]['id'] = $moduleid;
    $student_modules[$student_mod_count]['attempt'] = $attempt;
    $student_mod_count++;
  }
  $result->close();

  $module_no = 0;
  $old_year = '';  
  $modules = array();
  $mod_count = 0;
  
  $result = $mysqli->prepare("SELECT moduleid, fullname FROM modules, schools WHERE modules.schoolid=schools.id AND active=1 ORDER BY moduleid");
  $result->execute();
  $result->store_result();
  $result->bind_result($moduleid, $fullname);
  while ($row = $result->fetch()) {
    $modules[$mod_count]['id'] = $moduleid;
    $modules[$mod_count]['fullname'] = $fullname;
    $mod_count++;
  }
  $result->close();
  
  if ($mod_count == 0) {
    echo "<div style=\"color:#C00000\">&nbsp;<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" />&nbsp;No modules found for academic session <strong>" . $_GET['session'] . "</strong>.</div>";
  } else {
    list_modules($modules,1,$student_modules);
    list_modules($modules,2,$student_modules);
    list_modules($modules,3,$student_modules);
  }
 
  echo "<input type=\"hidden\" name=\"mod_count\" value=\"$mod_count\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" name=\"userID\" value=\"" . $_GET['userID'] . "\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" name=\"session\" value=\"" . $session . "\" /></div></td>\n</tr>\n";
?>
<br />
<div align="center"><input style="width:120px" type="submit" name="submit" value="OK" />&nbsp;<input style="width:120px" type="submit" name="cancel" value="Cancel" onclick="window.close()" /></div>

</form>
</body>
</html>
<?php
  }
  $mysqli->close();
?>