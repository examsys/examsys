<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

?>
<style>
   .cancel {margin-right:8px}
</style>

<div class="popup" style="z-index:20; font-size:90%; background-color:white; top:120px; width:630px; height:346px" id="copy_submenu">
<?php
if ($configObject->get_setting('core', 'cfg_summative_mgmt')) {
    echo '<form method="post" action="../paper/copy.php" id="checkcopy" autocomplete="off">';
} else {
    echo '<form method="post" action="../paper/copy.php" autocomplete="off">';
}
?>
<div id="table1div" style="width:100%">
<table cellpadding="1" cellspacing="0" border="0" style="font-size:100%; width:100%" id="table1">
<tr>
<td colspan="5" style="background-color:#F2B100; height:5px; padding:0"> </td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td style="text-align:right"><?php echo $string['copyname']; ?>&nbsp;</td>
<td colspan="4"><input type="text" id="new_paper" name="new_paper" maxlength="255" value="<?php echo $properties->get_paper_title() ?>" size="65" required />
<input type="hidden" name="paperID" value="<?php echo $paperID ?>" />
<input type="hidden" name="module" value="<?php echo $module ?>" />
<input type="hidden" name="folder" value="<?php echo $folder ?>" />
</td>
</tr>

<tr>
<td style="text-align:right"><?php echo $string['type'] ?>&nbsp;</td>
<td colspan="4"><select name="paper_type" id="paper_type" class="changepapertype">
<?php
switch ($properties->get_paper_type()) {
    case 0:
    case 1:
    case 2:
        $types = array(0 => $string['formative self-assessment'], 1 => $string['progress test'], 2 => $string['summative exam']);

        break;
    case 3:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  $types = array(3 => $string['survey']);

        break;
    case 4:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  $types = array(4 => $string['osce station']);

        break;
    case 5:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  $types = array(5 => $string['offline paper']);

        break;
    case 6:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  $types = array(6 => $string['peer review']);

        break;
}
foreach ($types as $type_no => $type_text) {
    if ($properties->get_paper_type() == $type_no) {
        echo "<option value=\"$type_no\" selected>$type_text</option>\n";
    } else {
        echo "<option value=\"$type_no\">$type_text</option>\n";
    }
}
?>
  </select></td></tr>
<?php
  echo '<tr><td style="text-align:right">' . $string['academicsession'] . '</td><td>';
echo "<select name=\"session\">\n";
$yearutils = new yearutils($mysqli);
echo $yearutils->get_calendar_year_dropdown_options($properties->get_paper_type(), $properties->get_calendar_year(), $string);
echo "</select></td>\n";
?>
</tr>
<tr>
<td></td>
<td colspan="4"><input type="checkbox" name="copy_std_setting" id="copy_std_setting" <?php if ($configObject->get_setting('core', 'stdset_copy_std_setting')) {
    echo 'checked="checked" ';
                                                                                     } ?>/> <label for="copy_std_setting"><?php echo $string['copystdsetting']; ?></label>
</td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr id="copytype_selector">
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" id="copytype_paperonly" value="paperonly" /><label for="copytype_paperonly"><?php echo $string['paperonly'] ?></label></td>
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" id="copytype_paperquestions" value="paperquestions" checked /><label for="copytype_paperquestions"><?php echo $string['paperandquestions'] ?></td>
<td>&nbsp;</td>
</tr>
<tr>
<td style="width:130px" align="center">&nbsp;</td>
<td style="width:170px" align="center"><label for="copytype_paperonly"><img src="../artwork/copy_paper_only.png" width="120" height="130" /></label></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td style="width:170px" align="center"><label for="copytype_paperquestions"><img src="../artwork/copy_paper_questions.png" width="120" height="130" /></label></td>
<td style="width:110px" align="center">&nbsp;</td>
</tr>
<tr>
<?php
if ($properties->get_paper_type() == '2' and $configObject->get_setting('core', 'cfg_summative_mgmt')) {
    echo '<td align="center" colspan="5"><input type="button" class="cancel" value="' . $string['cancel'] . '" name="cancel" id="cancel" /><input type="button" class="ok" value="' . $string['next'] . '" name="next" id="next_button" /><input type="submit" class="ok" style="display:none" value="' . $string['copypaper'] . '" name="submit" id="submit_button" /></td>';
} else {
    echo '<td align="center" colspan="5"><input type="button" class="cancel" value="' . $string['cancel'] . '" name="cancel" id="cancel" /><input type="button" class="ok" style="display:none" value="' . $string['next'] . '" name="next" id="next_button" /><input type="submit" class="ok" value="' . $string['copypaper'] . '" name="submit" id="submit_button" /></td>';
}
?>
</tr>
</table>
</div>

<div id="table2div" style="display:none; width:100%">
<table cellpadding="1" cellspacing="1" border="0" style="font-size:90%; width:100%" id="table2">
<tr>
<td colspan="6" style="background-color:#F2B100; height:5px; padding:0"> </td>
</tr>
<tr><td colspan="6">&nbsp;</td></tr>
<tr>
<?php
  $next_flag = 1;
echo '<tr><td style="text-align:right">' . $string['campus'] . '</td><td><select name="campus" style="width:120px">';
$campusobj = new campus($mysqli);
$campuses = $campusobj->get_all_campus_details();
foreach ($campuses as $key => $campusarray) {
    if ($campusarray['isdefault']) {
        echo '<option value="' . $campusarray['campusname'] . '" selected>' . $campusarray['campusname'] . '</option>';
    } else {
        echo '<option value="' . $campusarray['campusname'] . '">' . $campusarray['campusname'] . '</option>';
    }
}
  echo '</select></td><td style="text-align:right">' . $string['barriersneeded'] . '</td><td><input type="checkbox" name="barriers_needed" value="1" chacked="checked" /><td style="text-align:right">' . $string['duration'] . '</td><td>';
echo '<select name="duration_hours" id="duration_hours">';
echo "<option value=\"\"></option>\n";
for (
    $i = 0; $i <= 12; $i++
) {
    echo "<option value=\"$i\">$i</option>\n";
}
    echo '</select> ' . $string['hrs'] . ' <select name="duration_mins" id="duration_mins">';
echo "<option value=\"\"></option>\n";
for (
    $i = 0; $i < 60; $i++
) {
    echo "<option value=\"$i\">$i</option>\n";
}
    echo '</select> ' . $string['mins'] . '</td></tr>';
echo '<tr><td style="text-align:right">' . $string['daterequired'] . '</td><td><select name="period" id="period">';
$months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
echo "<option value=\"\"></option>\n";
for (
    $i = 0; $i < 12; $i++
) {
    echo "<option value=\"$i\">" . $string[$months[$i]] . "</option>\n";
}
  echo '</select></td><td style="text-align:right">' . $string['cohortsize'] . '</td><td><select name="cohort_size" id="cohort_size">';
echo "<option value=\"\"></option>\n";
$sizes = array('&lt;whole cohort&gt', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300', '301-400', '401-500');
foreach ($sizes as $size) {
    echo "<option value=\"$size\">$size</option>\n";
}
  echo '</select></td><td style="text-align:right">' . $string['sittings'] . '</td><td><select name="sittings">';
for (
    $i = 1; $i <= 6; $i++
) {
    echo "<option value=\"$i\">$i</option>";
}
  echo '</select></td></tr>';
echo '<tr><td colspan="6">&nbsp;</td></tr>';
echo '<tr><td style="text-align:right">' . $string['notes'] . '</td><td colspan="5"><textarea style="width:98%; height:77px" cols="40" rows="5" name="notes" style="text-size:90%"></textarea></td></tr>';
echo '<tr><td colspan="6">&nbsp;</td></tr>';
echo '<tr><td colspan="6">&nbsp;</td></tr>';
echo '<tr><td colspan="6" style="text-align:center"><input type="button" class="cancel" value="' . $string['cancel'] . '" name="cancel" id="cancel" data-popupid="copy_submenu" /><input type="submit" class="ok" value="' . $string['copypaper'] . '" name="submit" /></td></tr>';

?>
</table>
</div>

</form>
</div>
