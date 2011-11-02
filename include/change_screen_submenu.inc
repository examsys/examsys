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

<div class="popup" style="top:380px; width:450px" id="change_screen_submenu" onclick="showChangeScreen(event);">
<?php
  $line = 0;

  $itemID = $menuNo . '_' . $line;
  echo "<div class=\"popupitem\" id=\"$itemID\" onclick=\"incScreen()\" onmouseover=\"menuRowOn('$itemID');\" onmouseout=\"menuRowOff('$itemID');\">" . $string['insertscreenbreak'] . "</div>\n";
  echo '<div class="popupitem"><hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" /></div>';
  echo "<table>\n";
  for ($row=1; $row<=6; $row++) {
    echo '<tr>';
    for ($col=1; $col<=10; $col++) {
      $line++;
      $itemID = $menuNo . '_' . $line;
      echo "<td class=\"popupitem\" id=\"$itemID\" onclick=\"changeScreen($line)\" onmouseover=\"menuRowOn('$itemID');\" onmouseout=\"menuRowOff('$itemID');\">$line</td>";
    }
    echo "</tr>\n";
  }
  
  echo "</table>\n";
  $menuNo++;
?>
</div>