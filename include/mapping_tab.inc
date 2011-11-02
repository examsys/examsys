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
* Set of mapping functions used to map touchstone questions to session objectives. These functions 
* are used by the question editing and adding scripts to display the mapping tab.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once($cfg_web_root . 'include/mapping.inc');

/**
 * This is function displayMappingTab it displays mapping tab on edit pages
 *
 * @param mixed $paper_id 
 * @param mixed $touchstonedb a mysqli object
 * @param mixed $created date the question was created
 * @param mixed $modified date the question was last modified
 * 
 */
function displayMappingTab($paper_id, $touchstonedb, $created, $modified) {
  global $string;

  ?>
  <table cellpadding="0" cellspacing="0" border="0" id="mappingtab" style="display:none; font-size:90%; background-color:#DFECFF">
  <tr><td>
  <table cellpadding="0" cellspacing="0" border="0" style="width:504px; font-size:90%">
  <tr>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('editortab');"><?php echo $string['editor']; ?></td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('changestab');"><?php echo $string['changes']; ?></td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('commentstab');"><?php echo $string['comments']; ?></td>
    <td style="width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_on.gif)"><?php echo $string['mapping']; ?></td>
  </tr>
  </table>
  <td style="width:100%; background-color:#DFECFF; text-align:right"><strong><?php echo $string['created']; ?></strong>&nbsp;<?php echo $created; ?>&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $string['modified']; ?></strong>&nbsp;<?php echo $modified; ?>&nbsp;</td></tr>

  <tr>
  <td colspan="5" style="background-color:#1E3C7B">&nbsp;</td>
  </tr>
  
  <tr>
  <td colspan="5" style="padding:15px; background-color:white">
<?php
  echo displayObjectivesMappingForm($paper_id, $touchstonedb);
  echo "</td></tr>\n</table>";
}

/**
 * This is function displayMappingTabAdd it displays mapping tab on add pages
 *
 * @param mixed $paper_id 
 * @param mixed $touchstonedb a mysqli object
 * @param mixed $created date the question was created
 * 
 */
function displayMappingTabAdd($paper_id, $touchstonedb, $created) {
  global $string;	
  ?>
  <table cellpadding="0" cellspacing="0" border="0" id="mappingtab" style="display:none; font-size:90%; background-color:#DFECFF">
  <tr><td>
  <table cellpadding="0" cellspacing="0" border="0" style="width:252px; font-size:90%">
  <tr>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('editortab');"><?php echo $string['editor']; ?></td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_on.gif)" onclick="showTab('mappingtab');"><?php echo $string['mapping']; ?></td>
  </tr></table>
  </td>
  <td style="width:100%; background-color:#DFECFF; text-align:right"><strong><?php echo $string['created']; ?></strong>&nbsp;<?php echo $created; ?>&nbsp;&nbsp;&nbsp;</td>
  </tr>
  <tr><td colspan="3" style="background-color:#1E3C7B">&nbsp;</td></tr>

  <tr>
  <td colspan="3" style="padding-top:15px; padding-left:15px; padding-right:15px; background-color:#F1F5FB">
<?php
  if($paper_id != '') {
    echo displayObjectivesMappingForm($paper_id, $touchstonedb);
  } else {
    echo "<div>";
    echo "<h2 style=\"color:#316AC5; font-weight:bold\">" . $string['mappingdisabled'] . "</h2>";
    echo "<p>" . $string['questionnotonpaper'] . "</p>";
    echo "</div>";
  }
  echo "</td></tr>\n</table>";
}
?>