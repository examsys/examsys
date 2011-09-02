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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../classes/dateutils.class.php';

/**
 * Get a list of years for which the module has mapped objectives
 * @param debug_mysqli $mysqli
 * @param string $module The module ID
 * @return array List of years
 */
function get_existing_years($mysqli, $module) {
	$years = array();
	
	$result = $mysqli->prepare("SELECT DISTINCT calendar_year FROM objectives WHERE moduleID = ? ORDER BY calendar_year DESC");
	$result->bind_param('s', $module);
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
function checkCopyYearForm()
{
	var formOK = true;
	
	sourceList = document.getElementById('source_y');
	destList = document.getElementById('dest_y');

	sourceYear = sourceList.options[sourceList.selectedIndex].value;
	destYear = destList[destList.selectedIndex].value;

	if(typeof(sourceYear) != 'undefined' && typeof(destYear) != 'undefined'
		&& sourceYear != '' && destYear != '')
	{
		if(sourceYear == destYear)
		{
			alert('Source and destination years must be different.');
			formOK = false;
		}
	}
	else
	{
		alert('Please select years to copy from and to.');
		formOK = false;
	}

	return formOK;
}
</script>
<div style="display:none; position:fixed; left:190px; top:135px; filter: progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=4)" id="session_copy_menu" onclick="showSessCopyMenu(event);">
<table border="0" cellpadding="3" cellspacing="1" style="width:300px; border:1px solid #C0C0C0; font-size:80%; background-color:white">
<tr>
<td style="background-color:#F2B100; height:5px"> </td>
</tr>
<tr>
<td align="left">
<?php
	$message = '';

	// Get the current academic session
	$session = DateUtils::get_current_academic_year();
	$session_parts = explode('/',$session);
	
	// Get a list of existing (source) years
	$source_years = get_existing_years($mysqli, $_GET['module']);

	// Should never be a case where we don't have a source year but check anyway
	if(count($source_years) > 0) {
		$dest_years = array(end($source_years));
		
		// Get a list of destination years. Selected year will be removed by JS
		$start_year = substr(end($source_years), 0, 4);
		
		if(is_numeric($start_year)) {
			for($i = $start_year + 1; $i <= $session_parts[0] + 1; $i++) {
				$next = $i + 1;
				array_unshift($dest_years, $i . '/' . substr($next, 2, 2));
			}
		} else {
			$message = 'Invalid value in year list';
		}
	} else {
		$message = 'No years available to copy';
	}

	if($message != '') {	
?>
	<p><?php echo $message ?></p>
<?php
	} else {
?>
	<form action="copy_year.php" method="post" onsubmit="return checkCopyYearForm()">
		<table width="100%">
			<tr>
				<td width="50%">Copy objectives from</td>
				<td>
					<select id="source_y" name="source_y">
<?php
foreach($source_years as $s_year) {
?>
						<option value="<?php echo $s_year ?>"><?php echo $s_year ?></option>
<?php
}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Copy objectives to</td>
				<td>
					<select id="dest_y" name="dest_y">
<?php
$sel_shown = false;
$sel_mod = '';

if(count($dest_years) == 1) {
	$sel_mod = ' selected="selected"';
	$sel_shown = true;
}
?>
						<option value=""<?php echo $sel_mod ?>>&nbsp;</option>
<?php
foreach($dest_years as $d_year) {
if($sel_mod == '' and !$sel_shown and $d_year != $source_years[0]) {
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
				<td colspan="2" style="text-align: center"><input type="submit" value="Copy" /></td>
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


