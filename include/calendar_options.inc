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

?>
<div id="left-sidebar" class="sidebar">
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185">
<tr><td style="font-weight:bold"><?php echo $string['calendaryears']; ?></td></tr>
<tr><td>
<table cellpadding="3" cellspacing="0" border="0" style="margin-left:6px; font-size:90%">
<?php
  $start_year = date("Y");
  $start_year -= 4;
  for ($tmp_year=$start_year; $tmp_year < $start_year + 6; $tmp_year++) {
    echo "<tr><td><a href=\"calendar.php?calyear=$tmp_year\"><img src=\"../artwork/shortcut_calendar_icon.png\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"calendar.php?calyear=$tmp_year\">$tmp_year</a></td></tr>\n";
  }
?>
</table>

</td></tr>
</table>

</div>