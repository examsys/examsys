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
* Allows the properties of a paper to be edited.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/add_edit.inc';  // to clear MS Office tags
require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';
require '../lang/' . $language. '/include/timezones.inc';

function modulo($n,$b) {
  return $n-$b*floor($n/$b);
}

if (isset($_POST['Submit'])) {
  // Check that the new paper name is not already used by any other paper (i.e. unique).
  $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE paper_title=? LIMIT 1");
  $result->bind_param('s', $_POST['paper_title']);
  $result->execute();
  $result->bind_result($paper_title);
  $result->store_result();
  
  if ($result->num_rows == 0 or $_POST['original_paper_title'] == $_POST['paper_title']) {
    if ((modulo($_POST['fyear'],4) == 0 and modulo($_POST['fyear'],100) != 0) or modulo($_POST['fyear'],400) == 0) {
      $leap = true;
    } else {
      $leap = false;
    }   
    if ($leap == true and $_POST['fmonth'] == '02' and ($_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '29';
    if ($leap == false and $_POST['fmonth'] == '02' and ($_POST['fday'] == '29' or $_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '28';
    if (($_POST['fmonth'] == '04' or $_POST['fmonth'] == '06' or $_POST['fmonth'] == '09' or $_POST['fmonth'] == '11') and $_POST['fday'] == '31') $_POST['fday'] = '30';
    
    $local_time = new DateTimeZone($cfg_timezone);
    $target_timezone = new DateTimeZone($_POST['timezone']);
    
    $start_date = new dateTime($_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'], $target_timezone);
    $start_date->setTimezone($local_time);
        
    if ((modulo($_POST['tyear'],4) == 0 and modulo($_POST['tyear'],100) != 0) or modulo($_POST['tyear'],400) == 0) {
      $leap = true;
    } else {
      $leap = false;
    }   
    if ($leap == true and $_POST['tmonth'] == '02' and ($_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '29';
    if ($leap == false and $_POST['tmonth'] == '02' and ($_POST['tday'] == '29' or $_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '28';
    if (($_POST['tmonth'] == '04' or $_POST['tmonth'] == '06' or $_POST['tmonth'] == '09' or $_POST['tmonth'] == '11') and $_POST['tday'] == '31') $_POST['tday'] = '30';
    
    $end_date = new dateTime($_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'], $target_timezone);
    $end_date->setTimezone($local_time);
        
    if ($_POST['timezone'] < 0) {
      $start_date->modify("+" . abs($_POST['timezone']) . " hour");
      $end_date->modify("+" . abs($_POST['timezone']) . " hour");
    } elseif ($_POST['timezone'] > 0) {
      $start_date->modify("-" . $_POST['timezone'] . " hour");
      $end_date->modify("-" . $_POST['timezone'] . " hour");
    }

    $module_string = '';
    $first_module = '';
    for ($i=0; $i<$_POST['module_no']; $i++) {
      if (isset($_POST['module' . $i])) {
        if ($module_string == '') {
          $module_string = $_POST['module' . $i];
          $first_module = $_POST['module' . $i];
        } else {
          $module_string .= ',' . $_POST['module' . $i];
        }
      }
    }
    
    $lab_string = '';
    for ($i=0; $i<$_POST['lab_no']; $i++) {
      if (isset($_POST["lab$i"])) {
        if ($lab_string == '') {
          $lab_string = $_POST["lab$i"];
        } else {
          $lab_string .= ',' . $_POST["lab$i"];
        }
      }
    }
    
    $paper_title = $_POST['paper_title'];
    $tmp_start_date = $start_date->format("YmdHis");
    $tmp_end_date = $end_date->format("YmdHis");
    $timezone = $_POST['timezone'];
    $bgcolor = $_POST['bgcolor'];
    $fgcolor = $_POST['fgcolor'];
    $themecolor = $_POST['themecolor'];
    $labelcolor = $_POST['labelcolor'];
    $folderID = $_POST['folderID'];
    $password = trim($_POST['password']);
    $paperID = $_POST['paperID'];
    $tmp_marking = $_POST['marking'];
    if (isset($_POST['display_photos'])) {
      $display_photos = '1';       // Reuse the 'display_correct_answer' field to store this setting.
    } else {
      $display_photos = '0';
    }
    
    $display_question_mark = $_POST['review'];    // Reuse the 'display_question_mark' field to stor this setting.
    
    $rubric = $_POST['type'];      // Reuse the 'rubric' field to store which field in the metadata to use for groups.
    
    $tmp_prologue = $_POST['paper_prologue'];
    $tmp_prologue = clearMSOtags($tmp_prologue);
    
    $tmp_postscript = $_POST['paper_postscript'];
    $tmp_postscript = clearMSOtags($tmp_postscript);

    $editProperties = $mysqli->prepare("UPDATE properties SET paper_title=?, start_date=?, end_date=?, timezone=?, moduleID=?, bgcolor=?, fgcolor=?, themecolor=?, labelcolor=?, folder=?, labs=?, calendar_year=?, password=?, rubric=?, paper_prologue=?, paper_postscript=?, marking=?, display_correct_answer=?, display_question_mark=? WHERE property_id=?");
    $editProperties->bind_param('sssssssssssssssssssi', $paper_title, $tmp_start_date, $tmp_end_date, $timezone, $module_string, $bgcolor, $fgcolor, $themecolor, $labelcolor, $folderID, $lab_string, $_POST['calendar_year'], $password, $rubric, $tmp_prologue, $tmp_postscript, $tmp_marking, $display_photos, $display_question_mark, $paperID);
    $editProperties->execute();
    $editProperties->close();
    
    // Set the questions team on this paper.
    $result = $mysqli->prepare("SELECT q_id FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id ORDER BY display_pos");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id);
    while ($result->fetch()) {
      $editPaper = $mysqli->prepare("UPDATE questions SET q_group=? WHERE q_id=?");
      $editPaper->bind_param('si', $first_module, $q_id);
      $editPaper->execute();
      $editPaper->close();
    }
    $result->close();
  ?>
    <html>
    <head>
    <title><?php echo $string['edittitle']; ?></title>
    <meta http-equiv="pragma" content="no-cache" />
    <script language="JavaScript">
      function closeWindow() {
        window.opener.location = "/paper/details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>&folder=<?php if (isset($_POST['folderID'])) echo $_POST['folderID']; ?>&school=";
        window.close();
      }
      function updateParent() {
        window.opener.parent.location = "/paper/details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>";
        window.close();
      }
    </script></head>
    <body onload="closeWindow();">
    <form>
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="updateParent();" /></div>
    </form>
  <?php
  } else {
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
    <html>
    <head><title><?php echo $string['edittitle']; ?></title></head>
    <body>
    <form>
      <br /><?php echo $string['warning']; ?><br />&nbsp;<div align="center"><input type="button" name="back" value="&lt; Back" onclick="javascript: history.go(-1)" /></div>
    </form>
  <?php
  }
} else {
  $option_no = 1;
  
  // Get the main properties of the paper
  $result = $mysqli->prepare("SELECT paper_title, paper_type, start_date, end_date, timezone, bgcolor, fgcolor, themecolor, labelcolor, folder, labs, moduleID, calendar_year, password, crypt_name, paper_prologue, paper_postscript, marking, display_correct_answer AS display_photos, display_question_mark AS review FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title, $paper_type, $start_date, $end_date, $timezone, $bgcolor, $fgcolor, $themecolor, $labelcolor, $folder, $labs, $moduleID, $calendar_year, $password, $crypt_name, $paper_prologue, $paper_postscript, $marking, $display_photos, $review);
  $result->fetch();
  $result->close();
  
  $local_time = new DateTimeZone($cfg_timezone);
  $target_timezone = new DateTimeZone($timezone);
  $start_date = new dateTime($start_date, $local_time);
  $end_date = new dateTime($end_date, $local_time);
  
  $start_date->setTimezone($target_timezone);
  $end_date->setTimezone($target_timezone);
  
  $start_date = $start_date->format("Y/m/d H:i:s");
  $end_date = $end_date->format("Y/m/d H:i:s");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $string['propertiestitle'] . ' ' . $cfg_install_type; ?></title>

  <style>
    body {font-family:Arial,sans-serif; color:black; background-color:#F1F5FB; margin:0px; font-size:100%}
    table {font-size:100%; text-align:left}
    input,textarea {font-family:Arial,sans-serif; color:black}
    .indenton {text-indent:-23px; padding-left:23px; background-color:#B3C8E8}
    .indentoff {text-indent:-23px; padding-left:23px; background-color:white}
  </style>

  <?php echo $cfg_editor_javascript; ?>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript">
    function toggle(objectID) {
      if (document.getElementById(objectID).className == 'indentoff') {
        document.getElementById(objectID).className = 'indenton';
      } else {
        document.getElementById(objectID).className = 'indentoff';
      }
    }
    
    function checkForm() {
      if (edit_form.fyear.value > edit_form.tyear.value) {
        alert ("<?php echo $string['availablefromyear']; ?>");
        return false;
      } else if (edit_form.fyear.value == edit_form.tyear.value && edit_form.fmonth.value > edit_form.tmonth.value) {
        alert ("<?php echo $string['availablefrommonth']; ?>");
        return false;
      } else if (edit_form.fyear.value == edit_form.tyear.value && edit_form.fmonth.value == edit_form.tmonth.value && edit_form.fday.value > edit_form.tday.value) {
        alert ("<?php echo $string['availablefromday']; ?>");
        return false;
      }
      
      var module_no = document.getElementById('module_no').value;
      var moduleList = '';
      for (var i = 0; i < module_no; i++) {
        objectID = 'module' + i;        
        if (document.getElementById(objectID).checked == true) {
          if (moduleList == '') {
            moduleList = document.getElementById(objectID).value;
          } else {
            moduleList += ',' + document.getElementById(objectID).value;
          }
        }
      }
      if (moduleList == '') {
        alert ("<?php echo $string['msg1']; ?>");
        return false;
      }

      if (document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '4') {
        var module_no = document.getElementById('module_no').value;

        var moduleList = '';
        for (var i = 0; i < module_no; i++) {
          objectID = 'module' + i;        
          if (document.getElementById(objectID).checked == true) {
            if (moduleList == '') {
              moduleList = document.getElementById(objectID).value;
            } else {
              moduleList += ',' + document.getElementById(objectID).value;
            }
          }
        }
        if (moduleList == '') {
          alert ("<?php echo $string['msg5']; ?>");
          return false;
        }
      }
      
      if (edit_form.paper.value == '') {
        alert ("<?php echo $string['msg7']; ?>");
        return false;
      }    
    }

    function buttonclick(sectionID) {
      document.getElementById('general').style.display = 'none';
      document.getElementById('security').style.display = 'none';
      document.getElementById('prologue').style.display = 'none';
      document.getElementById('postscript').style.display = 'none';
      document.getElementById(sectionID).style.display='';
      
      document.getElementById('button_general').style.background='';
      document.getElementById('button_security').style.background='';
      document.getElementById('button_prologue').style.background='';
      document.getElementById('button_postscript').style.background='';

      document.getElementById('button_'+sectionID).style.background='url("../artwork/2007_button_on.png")';
    }

    function buttonover(buttonID) {
      if (document.getElementById('button_'+buttonID).style.backgroundImage != 'url("../artwork/2007_button_on.png")') {
        document.getElementById('button_'+buttonID).style.backgroundImage='url("../artwork/2007_button_over.png")';
      }
    }

    function buttonout(buttonID) {
      
      if (document.getElementById('button_'+buttonID).style.backgroundImage != 'url("../artwork/2007_button_on.png")') {
        document.getElementById('button_'+buttonID).style.backgroundImage='';
      }
    }
  </script>
</head>
<body onload="window.focus()" onclick="hidePicker();">
<form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php
  require '../tools/colour_picker/colour_picker.inc';
?>
<table border="0" cellpadding="1" cellspacing="5" style="width:100%; height:645px; font-size:90%">
<tr><td valign="top" style="background-color:white; border:1px solid #7F9DB9; width:120px">

<table cellspacing="0" cellpadding="0" border="0" style="font-size:90%; width:120px">
<tr><td id="button_general" style="background-image:url('../artwork/2007_button_on.png'); height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('general')" onmouseout="buttonout('general')" onclick="buttonclick('general')">&nbsp;<?php echo $string['generaltab']; ?></td></tr>
<tr><td id="button_security" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('security')" onmouseout="buttonout('security')" onclick="buttonclick('security')">&nbsp;<?php echo $string['securitytab']; ?></td></tr>
<tr><td id="button_prologue" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('prologue')" onmouseout="buttonout('prologue')" onclick="buttonclick('prologue')">&nbsp;<?php echo $string['prologuetab']; ?></td></tr>
<tr><td id="button_postscript" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('postscript')" onmouseout="buttonout('postscript')" onclick="buttonclick('postscript')">&nbsp;<?php echo $string['postscripttab']; ?></td></tr>
</table>

</td>

<td style="background-color:white; border:1px solid #7F9DB9" valign="top">

<table id="general" style="height:590px; width:100%; font-size:90%<?php if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') echo ';display:none'; ?>" cellpadding="0" cellspacing="0" border="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/general_heading_icon.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['generalheading']; ?></td></tr>
<td style="text-align:left; vertical-align:top" colspan="2">
   <?php
     echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA; color:#00156E; border-bottom: 1px solid #CFDBEB\">&nbsp;" . $string['paperdetails'] . "</td></tr>\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['url'] . "&nbsp;</td><td colspan=\"3\"><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/peer_review/form.php?id=" . urlencode($crypt_name) ."\" target=\"_blank\" style=\"color:blue\">" . $protocol . $_SERVER['HTTP_HOST'] . "/peer_review/form.php?id=" . urlencode($crypt_name) ."</a></td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['name'] . "&nbsp;</td><td colspan=\"3\"><input type=\"text\" size=\"75\" maxlength=\"255\" value=\"$paper_title\" name=\"paper_title\" /><input type=\"hidden\" name=\"original_paper_title\" value=\"$paper_title\"><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\"></td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['folder'] . "&nbsp;</td><td colspan=\"3\" valign=\"top\">\n<select style=\"width:210px\" name=\"folderID\">\n";
     echo "<option value=\"\"></option>";
     $additional = '';
     
     $team_query = $mysqli->prepare("SELECT DISTINCT name FROM teams WHERE memberID=? ORDER BY name");
     $team_query->bind_param('s', $userID);
     $team_query->execute();
     $team_query->store_result();
     $team_query->bind_result($team_name);
     while ($team_query->fetch()) {
       if ($team_name != '') {
         if ($additional == '') {
           $additional = ' OR team_name IN ("' . $team_name . '"';
         } else {
           $additional .= ',"' . $team_name . '"';
         }
       }
     }
     $team_query->close();
     
    if ($additional != '') $additional .= ')';
    if ($folder != '') $additional .= ' OR id=' . $folder;
     
    $folder_details = $mysqli->prepare("SELECT id, name FROM folders WHERE ownerID=? $additional ORDER BY name");
    $folder_details->bind_param('s', $userID);
    $folder_details->execute();
    $folder_details->bind_result($folder_id, $folder_name);
    while ($folder_details->fetch()) {
      $path_parts = substr_count($folder_name,';');
      $folder_array = explode(';',$folder_name);
      $display_name = str_repeat('&nbsp;',$path_parts * 4) . $folder_array[$path_parts];
      if ($folder == $folder_id) {
        echo "<option value=\"" . $folder_id . "\" selected>" . $display_name . "</option>";
      } else {
        echo "<option value=\"" . $folder_id . "\">" . $display_name . "</option>";
      }
    }
    $folder_details->close();
    echo "</select>\n</td></tr>\n";
     
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";     

    echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA;color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;" . $string['displayoptions'] ."</td></tr>\n";
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     
    echo "<tr>\n";
    echo "<td align=\"right\">" . $string['background'] . "&nbsp;</td><td><div onclick=\"showPicker('bgcolor',event)\" id=\"span_bgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$bgcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"bgcolor\" name=\"bgcolor\" value=\"$bgcolor\" /></td>";
    echo "<td align=\"right\">" . $string['foreground'] . "&nbsp;</td><td><div onclick=\"showPicker('fgcolor',event)\" id=\"span_fgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$fgcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"fgcolor\" name=\"fgcolor\" value=\"$fgcolor\" /></td>";
    echo "</tr>\n";
 
    echo "<tr>\n";
    echo "<td align=\"right\">Peer Names&nbsp;</td><td><div onclick=\"showPicker('themecolor',event)\" id=\"span_themecolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$themecolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" /></td>";
    echo "<td align=\"right\">Headings&nbsp;</td><td><div onclick=\"showPicker('labelcolor',event)\" id=\"span_labelcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$labelcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" /></td>";
    echo "</tr>\n";
    
    echo "<tr><td align=\"right\">Photos&nbsp;</td><td colspan=\"3\">";
    if ($display_photos == '1') {
      echo "<input type=\"checkbox\" name=\"display_photos\" value=\"1\" checked />";
    } else {
      echo "<input type=\"checkbox\" name=\"display_photos\" value=\"1\" />";
    }
    echo "&nbsp;if available</td></tr>\n";

    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";

    echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;Form</td></tr>\n";
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Group Details&nbsp;</td><td><select name=\"type\">\n";
    
    $field_details = $mysqli->prepare("SELECT DISTINCT type FROM users_metadata, modules WHERE users_metadata.moduleID=modules.id AND modules.moduleid=? ORDER BY type");
    $field_details->bind_param('s', $moduleID);
    $field_details->execute();
    $field_details->bind_result($type);
    while ($field_details->fetch()) {
      echo "<option value=\"$type\">$type</option>\n";
    }
    $field_details->close();
    echo "</select>\n</td>\n";
    echo "<td align=\"right\">Number from</td><td><select name=\"marking\">\n";
    if ($marking == '1') {
      echo "<option value=\"0\">0</option>\n<option value=\"1\" selected>1</option>\n";
    } else {
      echo "<option value=\"0\" selected>0</option>\n<option value=\"1\">1</option>\n";
    }
    echo "</select>\n</td></tr>\n";
    echo '<tr><td align="right">Review</td><td>';
    if ($review == '1') {
      echo '<input type="radio" name="review" value="1" checked="checked" />';
    } else {
      echo '<input type="radio" name="review" value="1" />';
    }
    echo 'All peers per group<br />';
    if ($review == '0') {
      echo '<input type="radio" name="review" value="0" checked="checked" />';
    } else {
      echo '<input type="radio" name="review" value="0" />';
    }
    echo 'Single Review</td></tr>';
  ?>
  </table>
</td>
</tr>
</table>

<?php
  echo '<table id="security" style="width:100%; font-size:90%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">';
?>
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/security_heading_icon.png" width="30" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['securityheading']; ?></td></tr>
<tr>
<td style="text-align:center; vertical-align:top" colspan="2">
<?php
    echo "<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" style=\"width:100%; padding-bottom:10px\">\n";
    echo "<tr><td align=\"right\">" . $string['session'] . "</td><td><select name=\"calendar_year\" id=\"session\" onchange=\"getMetadataDropdowns();\">\n<option value=\"\">" . $string['na'] .  "</option>\n";
    $academic_years = array('2002/03','2003/04','2004/05','2005/06','2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16');
    foreach ($academic_years as $value) {
      echo "<option value=\"" . $value . "\"";
      if ($calendar_year == $value) echo 'selected';
      echo ">";
      echo $value . "</option>\n";
    }    
    echo "</select></td><td align=\"right\">" . $string['password'] . "</td><td><input type=\"text\" size=\"20\" name=\"password\" value=\"$password\" /></td></tr>\n";

    echo "<tr><td align=\"right\">" . $string['timezone'] .  "</td><td><select name=\"timezone\">";
    foreach ($timezone_array as $individual_zone => $display_zone) {
      if ($timezone == $individual_zone) {
        echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
      } else {
        echo "<option value=\"$individual_zone\">$display_zone</option>";
      }
    }
    echo '</select></td>';
    echo '<td></td><td></td></tr>';
    echo "<tr><td align=\"right\" valign=\"top\">" . $string['availablefrom'] . "</td><td>";

    // Split the start date
    $split_year = substr($start_date,0,4);
    $split_month = substr($start_date,5,2);
    $split_day = substr($start_date,8,2);
    $split_hour = substr($start_date,11,2);
    $split_minute = substr($start_date,14,2);

     // Available from Day
    echo "<select name=\"fday\"\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          echo "<option value=\"$i\" selected>";
        } else {
          echo "<option value=\"$i\">";
        }
      }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
   // Available from Month
    echo "<select name=\"fmonth\"\">\n";
    $months = array('january','february','march','april','may','june','july','august','september','october','november','december');
    for ($i=0; $i<12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>$trans_month</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">$trans_month</option>\n";
        }
      }
    }
    echo "</select>\n";
    // Available from Year
    echo "<select name=\"fyear\"\">\n";
    for ($i = 2002; $i < 2021; $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>\n<select name=\"ftime\">\n";
    // Available from Hour
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00') {
        echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
      } else {
        echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
      }
    }
    echo "</select>\n</td>\n";

    // Split the end date
    $split_year = substr($end_date,0,4);
    $split_month = substr($end_date,5,2);
    $split_day = substr($end_date,8,2);
    $split_hour = substr($end_date,11,2);
    $split_minute = substr($end_date,14,2);

    echo "<td align=\"right\">" . $string['to'] . "&nbsp;</td><td>";
    
     // Available from Day
    echo "<select name=\"tday\"\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          echo "<option value=\"$i\" selected>";
        } else {
          echo "<option value=\"$i\">";
        }
      }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";

    // Available to Month
    echo "<select name=\"tmonth\"\">\n";
    for ($i=0; $i<12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>$trans_month</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">$trans_month</option>\n";
        }
      }
    }
    echo "</select>\n";
    // Available to Year
    echo "<select name=\"tyear\"\">\n";
    for ($i = 2002; $i < (date('Y')+21); $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>&nbsp;<select name=\"ttime\">\n";
    // Available to Hour
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00') {
        echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
      } else {
        echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
      }
    }
    echo "</select>\n</td></tr>\n";
    echo "</table>\n";

    echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\">\n";
    echo "<tr><td style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB; padding:2px; width:400px\">&nbsp;" . $string['modules'] . "</td><td style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB; padding:2px\">&nbsp;" . $string['restricttolabs'] . "</td></tr>";
    echo "<tr><td rowspan=\"3\" style=\"vertical-align:top\">";
    
    echo "<div style=\"display:block; width:400px; height:425px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
    
    $modules_array = explode(',',$moduleID);
    $total_modules = array_merge($teams, $modules_array);
    
    $module_sql = implode("','", $total_modules);
    if ($module_sql != '') $module_sql = "'$module_sql'";
    
    if ($module_sql == '') {
      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"0\" /></div>\n";
    } else {
      $module_array = SearchUtils::getTeams($teams, $userroles, $userID, $mysqli);
      $module_no = 0;
      $old_school = '';
      foreach ($module_array as $module) {
        if ($module['school'] != $old_school) {
          echo "<div style=\"padding-top:2px\"><strong>" . $module['school'] . "</strong></div>";
        }
        $match = false;
        foreach ($modules_array as $separate_module) {
          if ($separate_module == $module['id']) $match = true;
        }
        if ($match == true) {
          if (in_array($module['id'],$teams) or strpos($userroles,'SysAdmin') !== false) {
            echo "<div class=\"indenton\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMetadataDropdowns();\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['id'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
          } else {
            echo "<div class=\"indenton\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['id'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $module['id'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
          }
        } else {
          echo "<div class=\"indentoff\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMetadataDropdowns();\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['id'] . "\">&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
        }
        $module_no++;  
        $old_school = $module['school'];        
      }
      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
    }
    echo "</td>\n";
    
    echo "<td><div style=\"height:425px; overflow-y:scroll;border:1px solid #7F9DB9; font-size:90%\">";
    $current_labs = explode(',',$labs);
    
    $lab_details = $mysqli->prepare("SELECT labs.id, name, campus, COUNT(ip_addresses.id) FROM labs, ip_addresses WHERE labs.id=ip_addresses.lab GROUP BY ip_addresses.lab ORDER BY campus, name");
    $lab_details->execute();
    $lab_details->bind_result($lab_id, $lab_name, $lab_campus, $computer_no);
    $lab_no = 0;
    $old_campus = '';
    while ($lab_details->fetch()) {
      if ($old_campus != $lab_campus) {
        echo "<div><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"lab\" />&nbsp;<strong>$lab_campus</strong></div>\n";
      }
      $match = false;
      foreach ($current_labs as $individual_lab) {
        if ($lab_id == $individual_lab) $match = true;
      }
      if ($match) {
        echo "<div class=\"indenton\" style=\"padding-left:40px\" id=\"divlab$lab_no\"><input type=\"checkbox\" onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" value=\"$lab_id\" checked>&nbsp;$lab_name <span style=\"color:#808080\">($computer_no)</span></div>\n";
      } else {
        echo "<div class=\"indentoff\" style=\"padding-left:40px\" id=\"divlab$lab_no\"><input type=\"checkbox\" onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" value=\"$lab_id\">&nbsp;$lab_name <span style=\"color:#808080\">($computer_no)</span></div>\n";
      }
      $lab_no++;
      $old_campus = $lab_campus;
    }
    $lab_details->close();
    echo "<input type=\"hidden\" name=\"lab_no\" value=\"$lab_no\" /></div></td>\n</tr>";

  ?>
  </td></tr>
  </table>
  </td></tr>
</table>

<table id="prologue" style="width:100%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/prologue_heading_icon.png" width="22" height="29" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['prologueheading']; ?></td></tr>
  <?php
    echo "<tr><td>" . wysiwyg_editor('oEdit2','paper_prologue',$paper_prologue,688,498);
  ?>
</td></tr>
</table>

<table id="postscript" style="width:100%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/postscript_heading_icon.png" width="22" height="29" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['postscriptheading']; ?></td></tr>
<?php
    echo "<tr><td>" . wysiwyg_editor('oEdit3','paper_postscript',$paper_postscript,688,498);
  ?>
</td></tr>
</table>

</td>
</tr>
<tr><td colspan="2" align="right"><input type="submit" style="width:100px" name="Submit" value="<?php echo $string['ok']; ?>">&nbsp;<input type="button" name="home" style="width:100px" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></td></tr>
</table>
</form>
<?php
  }
$mysqli->close();
?>
</body>
</html>
