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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

/**
 * Get a list of years for which the module has mapped objectives
 * @param debug_mysqli $db
 * @param int $idMod The DB ID of the module
 * @return array List of years
 */
function get_existing_years($db, $idMod) {
	$years = array();
	
	$result = $db->prepare("SELECT DISTINCT calendar_year FROM objectives WHERE idMod = ? ORDER BY calendar_year DESC");
	$result->bind_param('s', $idMod);
  $result->execute();
  $result->bind_result($year);
  while($result->fetch()) {
  	$years[] = $year;
  }
  $result->close();

  return $years;
}
?>
<script type="text/javascript">
  function checkCopyYearForm() {
    var formOK = true;
    
    sourceList = document.getElementById('source_y');
    destList = document.getElementById('dest_y');

    sourceYear = sourceList.options[sourceList.selectedIndex].value;
    destYear = destList[destList.selectedIndex].value;

    if (typeof(sourceYear) != 'undefined' && typeof(destYear) != 'undefined' && sourceYear != '' && destYear != '') {
      if (sourceYear == destYear) {
        alert('<?php echo $string['msg1']; ?>');
        formOK = false;
      }
    }	else {
      alert('<?php echo $string['msg2']; ?>');
      formOK = false;
    }

    return formOK;
  }
</script>
<div class="popup" style="display:none; position:fixed; left:190px; top:135px" id="session_copy_menu" onclick="showSessCopyMenu(event);">
<table border="0" cellpadding="2" cellspacing="0" style="width:300px; border:0px solid #C0C0C0; background-color:white">
<tr>
<td style="background-color:#F2B100; height:3px"></td>
</tr>
<tr>
<td align="left">
<?php
	$message = '';

	// Get the current academic session
	$session = date_utils::get_current_academic_year();
	$session_parts = explode('/', $session);
	
	// Get a list of existing (source) years
	$source_years = get_existing_years($mysqli, $idMod);

	// Should never be a case where we don't have a source year but check anyway
	if (count($source_years) > 0) {
    $cur_year = date_utils::get_current_academic_year();
		$dest_years = array(date_utils::get_current_academic_year(), date_utils::inc_academic_year($cur_year));

		// Get a list of destination years. Selected year will be removed by JS
		$start_year = intval(substr($session, 0, 4));
		
		if (is_numeric($start_year)) {

		} else {
			$message = $string['msg3'];
		}
	} else {
		$message = $string['msg4'];
	}

	if ($message != '') {	
?>
	<p><?php echo $message ?></p>
<?php
	} else {
?>
	<form action="copy_year.php" method="post" onsubmit="return checkCopyYearForm()">
		<table width="100%">
			<tr>
				<td width="50%"><?php echo $string['msg5']; ?></td>
				<td>
					<select id="source_y" name="source_y">
<?php
foreach ($source_years as $s_year) {
?>
						<option value="<?php echo $s_year ?>"><?php echo $s_year ?></option>
<?php
}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php echo $string['msg6']; ?></td>
				<td>
					<select id="dest_y" name="dest_y">
<?php
$sel_shown = false;
$sel_mod = '';

if (count($dest_years) == 1) {
	$sel_mod = ' selected="selected"';
	$sel_shown = true;
}

foreach ($dest_years as $d_year) {
  if ($sel_mod == '' and !$sel_shown and $d_year != $source_years[0]) {
    $sel_mod = ' selected="selected"';
    $sel_shown = true;
  } else {
    $sel_mod = '';
  }
?>
						<option value="<?php echo $d_year ?>"<?php echo $sel_mod ?>><?php echo $d_year ?></option>
<?php
$sel_mod = '';
}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center"><input type="submit" value="  <?php echo $string['copy']; ?>  " /></td>
			</tr>
		</table>
		<input type="hidden" name="moduleID" value="<?php echo $_GET['module'] ?>" />
	</form>
<?php
	}
?>
</td>
</tr>
</table>
</div>


