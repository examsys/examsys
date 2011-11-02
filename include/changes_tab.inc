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
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
?>
  <table cellpadding="0" cellspacing="0" border="0" id="changestab" style="display:none; font-size:90%; background-color:white">
  <tr><td>
  <table cellpadding="0" cellspacing="0" border="0" style="width:504px; font-size:90%">
  <tr style="background-color:#F0F0E8">
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('editortab');">Editor</td>
    <td style="width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_on.gif)">Changes</td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('commentstab');">Comments</td>
    <?php
    if (strpos($_SERVER['PHP_SELF'],'edit_info.php') !== false or strpos($_SERVER['PHP_SELF'],'edit_likert.php') !== false) {  // Disabled the mapping tab.
      echo "<td style=\"width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_disabled.gif)\">Mapping</td>\n";
    } else {
      echo "<td style=\"cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)\" onclick=\"showTab('mappingtab');\">Mapping</td>\n";
    }
    ?>
  </tr></table>
  <td style="width:100%; background-color:#DFECFF; text-align:right"><strong>Created:</strong>&nbsp;<?php echo $created; ?>&nbsp;&nbsp;&nbsp;&nbsp;<strong>Modified:</strong>&nbsp;<?php echo $modified; ?>&nbsp;</td></td></tr>
  <tr>
  <td colspan="5">
  
  <table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; width:100%">
<?php
  echo "<tr style=\"background-color:#1E3C7B; color:white; font-weight:bold\">";
  echo "<td style=\"width:10%\">&nbsp;Date</td>\n";
  echo "<td style=\"width:10%\">Section</td>\n";
  echo "<td style=\"width:34%\">Old</td>\n";
  echo "<td style=\"width:34%\">New</td>\n";
  echo "<td style=\"width:12%\">Editor</td></tr>\n";

  $changes = $mysqli->prepare("SELECT part, old, new, DATE_FORMAT(changed, '%d/%m/%Y') AS display_changed, title, initials, surname FROM (track_changes, users) WHERE track_changes.editor=users.id AND typeID=? ORDER BY changed DESC, users.id LIMIT 200");
  $changes->bind_param('i', $_GET['q_id']);
  $changes->execute();
  $changes->bind_result($part, $old, $new, $display_changed, $title, $initials, $surname);
  while ($row = $changes->fetch()) {
    $new = strip_tags($new);
    $new = str_replace('&nbsp;','',$new);
    if (strlen($new) > 100) $new = substr($new, 0, 100) . '...';
    
    $old = strip_tags($old);
    $old = str_replace('&nbsp;','',$old);
    if (strlen($old) > 100) $old = substr($old, 0, 100) . '...';
    
    if ($new == '') $new = '&nbsp;';
    if ($old == '') $old = '&nbsp;';

    echo '<tr><td class="changerow">&nbsp;' . $display_changed . '&nbsp;</td><td class="changerow">' . $part . '</td><td class="changerow">' . $old . '</td><td class="changerow">' . $new . '</td><td class="changerow">' . $title . ' ' . $initials . ' ' . $surname . "</td></tr>\n";
  }
  $changes->close();
  echo "</table>\n</td></tr>\n</table>";
?>
