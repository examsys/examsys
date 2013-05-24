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

require_once '../config/campuses.inc';
require_once '../classes/dateutils.class.php';
?>

<script language="JavaScript">
  function changeButton() {
    if ($("#paper_type").val() == '2') {
      document.getElementById('next_button').style.display = 'Block';
      document.getElementById('submit_button').style.display = 'None';
    } else {
      document.getElementById('submit_button').style.display = 'Block';
      document.getElementById('next_button').style.display = 'None';
    }
  }
  
  function nextTable() {
    document.getElementById('table1div').style.display = 'None';
    document.getElementById('table2div').style.display = 'Block';
  }
  
  function checkCopyForm() {
    if ($("#paper_type").val() == '2') {
      periodSelect = document.getElementById('period');
      if (periodSelect.options[periodSelect.selectedIndex].text == '') {
        alert ("<?php echo $string['msg7']; ?>");
        return false;
      }
      
      durationSelect = document.getElementById('duration');
      if (durationSelect.options[durationSelect.selectedIndex].text == '') {
        alert ("<?php echo $string['msg8']; ?>");
        return false;
      }
      
      cohortsizeSelect = document.getElementById('cohort_size');
      if (cohortsizeSelect.options[cohortsizeSelect.selectedIndex].text == '') {
        alert ("<?php echo $string['msg9']; ?>");
        return false;
      }
    }
  }
</script>

<div class="popup" style="z-index:20; display:none; font-size:90%; background-color:white; position:fixed; left:200px; top:100px; width:630px; height:320px" id="copy_submenu" onclick="showCopyMenu(true, event);">
<?php
if ($configObject->get('cfg_summative_mgmt')) {
  echo '<form method="post" action="../paper/copy.php" onsubmit="return checkCopyForm()">';
} else {
  echo '<form method="post" action="../paper/copy.php">';
}
?>
<div id="table1div" style="width:100%">
<table cellpadding="1" cellspacing="0" border="0" style="font-size:100%; width:100%" id="table1">
<tr>
<td colspan="5" style="background-color:#F2B100; height:5px; padding:0px"> </td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td style="text-align:right"><?php echo $string['copyname']; ?>&nbsp;</td>
<td colspan="4"><input type="text" name="new_paper" maxlength="255" value="<?php echo $properties->get_paper_title(); ?>" size="65" />
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input type="hidden" name="module" value="<?php echo $module; ?>" />
<input type="hidden" name="folder" value="<?php echo $folder; ?>" />
</td>
</tr>

<tr>
<td style="text-align:right"><?php echo $string['type']; ?>&nbsp;</td>
<td colspan="4"><select name="paper_type" id="paper_type" onchange="changeButton()">
<?php
  switch ($properties->get_paper_type()) {
    case 0:
    case 1:
    case 2:
      $types = array(0=>$string['formative quiz'], 1=>$string['progress test'], 2=>$string['summative exam']);
      break;
    case 3:
      $types = array(3=>$string['survey']);
      break;
    case 4:
      $types = array(4=>$string['osce station']);
      break;
    case 5:
      $types = array(5=>$string['offline paper']);
      break;
    case 6:
      $types = array(6=>$string['peer review']);
      break;
  }
  foreach ($types as $type_no=>$type_text) {
    if ($properties->get_paper_type() == $type_no) {
      echo "<option value=\"$type_no\" selected>$type_text</option>\n";
    } else {
      echo "<option value=\"$type_no\">$type_text</option>\n";
    }
  }
?>
</select></td>

</tr>

<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" value="paperonly" />&nbsp;<?php echo $string['paperonly']; ?></td>
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" value="paperquestions" checked />&nbsp;<?php echo $string['paperandquestions']; ?></td>
<td>&nbsp;</td>
</tr>
<tr>
<td style="width:110px" align="center">&nbsp;</td>
<td style="width:170px" align="center"><img src="../artwork/copy_paper_only.png" width="120" height="130" /></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td style="width:170px" align="center"><img src="../artwork/copy_paper_questions.png" width="120" height="130" /></td>
<td style="width:110px" align="center">&nbsp;</td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<?php
if ($properties->get_paper_type() == '2' and $configObject->get('cfg_summative_mgmt')) {
  echo '<td align="center" colspan="5"><input type="button" style="width:100px; display_block" value="' . $string['next'] . '" name="next" onclick="nextTable();" id="next_button" /><input type="submit" style="width:100px; display:none" value="' . $string['copypaper'] . '" name="submit" id="submit_button" /></td>';
} else {
  echo '<td align="center" colspan="5"><input type="button" style="width:100px; display:none" value="' . $string['next'] . '" name="next" onclick="nextTable();" id="next_button" /><input type="submit" style="width:100px; display:block" value="' . $string['copypaper'] . '" name="submit" id="submit_button" /></td>';
}
?>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
</table>
</div>

<div id="table2div" style="display:none; width:100%">
<table cellpadding="1" cellspacing="1" border="0" style="font-size:90%; width:100%" id="table2">
<tr>
<td colspan="6" style="background-color:#F2B100; height:5px; padding:0px"> </td>
</tr>
<tr><td colspan="6">&nbsp;</td></tr>
<tr>
<?php
  $next_flag = 1;
  echo '<tr><td style="text-align:right">' . $string['academicsession'] . '</td><td>';
  $module_details = $mysqli->prepare("SELECT DISTINCT calendar_year FROM modules_student ORDER BY calendar_year DESC");
  $module_details->execute();
  $module_details->bind_result($calendar_year);
  echo "<select name=\"session\">\n";
  while ($module_details->fetch()) {
    if ($next_flag == 1) {
      $next_session = (substr($calendar_year,0,4) + 1) . '/' . (substr($calendar_year,-2) + 1);
      $sel = (date_utils::get_current_academic_year() == $next_session) ? ' selected="selected"' : '';
      echo "<option value=\"$next_session\"$sel>$next_session</option>\n";
      $next_flag = 0;
    }
    $sel = (date_utils::get_current_academic_year() == $calendar_year) ? ' selected="selected"' : '';
    echo "<option value=\"$calendar_year\"$sel>$calendar_year</option>\n";
  }
  echo "</select></td>\n";

  echo '<td style="text-align:right">' . $string['barriersneeded'] . '</td><td><input type="checkbox" name="barriers_needed" value="1" chacked="checked" /><td style="text-align:right">Duration</td><td><select name="duration" id="duration">';
  $minutes = array('15'=>'15','20'=>'20','25'=>'25','30'=>'30','35'=>'35','40'=>'40','45'=>'45','50'=>'50','55'=>'55','60'=>'60','65'=>'65','70'=>'70','75'=>'75','80'=>'80','85'=>'85','90'=>'90','95'=>'95','100'=>'100','110'=>'110','120'=>'120','150'=>'150','180'=>'180');
  echo "<option value=\"\"></option>\n";
  foreach ($minutes as $key => $value) {
    echo "<option value=\"" . $key . "\">$value</option>\n";
  }
  echo '</select> ' . $string['mins'] . '</td></tr>';
  echo '<tr><td style="text-align:right">' . $string['daterequired'] . '</td><td><select name="period" id="period">';
  $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
  echo "<option value=\"\"></option>\n";
  for ($i=0; $i<12; $i++) {
    echo "<option value=\"$i\">" . $string[$months[$i]] . "</option>\n";
  }
  echo '</select></td><td style="text-align:right">' . $string['cohortsize'] . '</td><td><select name="cohort_size" id="cohort_size">';
  echo "<option value=\"\"></option>\n";
  $sizes = array('&lt;whole cohort&gt', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300', '301-400', '401-500');
  foreach ($sizes as $size) {
    echo "<option value=\"$size\">$size</option>\n";
  }
  echo '</select></td><td style="text-align:right">' . $string['sittings'] . '</td><td><select name="sittings">';
  for ($i=1; $i<=6; $i++) {
    echo "<option value=\"$i\">$i</option>";
  }
  echo '</select></td></tr>';
  
  echo '<tr><td style="text-align:right">' . $string['campus'] . '</td><td colspan="5"><select name="campus">';
  foreach ($cfg_campus_list as $campus) {
    if ($campus == $cfg_campus_default) {
      echo "<option value=\"$campus\" selected>$campus</option>";
    } else {
      echo "<option value=\"$campus\">$campus</option>";
    }
  }
  echo '</select></td></tr>';

  echo '<tr><td colspan="6">&nbsp;</td></tr>';
  echo '<tr><td style="text-align:right">' . $string['notes'] . '</td><td colspan="5"><textarea style="width:98%; height:77px" cols="40" rows="5" name="notes" style="text-size:90%"></textarea></td></tr>';
  
  echo '<tr><td colspan="6">&nbsp;</td></tr>';
  echo '<tr><td colspan="6">&nbsp;</td></tr>';
  echo '<tr><td colspan="6" style="text-align:center"><input type="submit" style="width:100px" value="' . $string['copypaper'] . '" name="submit" /></td></tr>';

?>
</table>
</div>

</form>
</div>