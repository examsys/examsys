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
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
?>
  <table cellpadding="0" cellspacing="0" border="0" id="commentstab" style="display:none; font-size:90%; background-color:#F0F0E8">
  <tr><td>
  <table cellpadding="0" cellspacing="0" border="0" style="width:504px; font-size:90%">
  <tr>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('editortab');">Editor</td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_off.gif)" onclick="showTab('changestab');">Changes</td>
    <td style="width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/artwork/tab_on.gif)">Comments</td>
    <?php
    if (strpos($_SERVER['PHP_SELF'],'info.php') !== false or strpos($_SERVER['PHP_SELF'],'likert.php') !== false) {  // Disabled the mapping tab.
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
  <tr style="background-color:#1E3C7B; color:white; font-weight:bold">
  <td style="width:16px"></td>
  <td style="width:14%">Reviewer</td>
  <td style="width:39%">Comments</td>
  <td style="width:5%">Action&nbsp;Taken</td>
  <td style="width:39%">Internal Response</td>
  </tr>
<?php
  $comment_no = 0;
  $old_paper_title = '';
  if (isset($_GET['paperID']) and $_GET['paperID'] != '') {
    $changes = $mysqli->prepare("SELECT paper_title, review_comments.id, category, comment, reviewed, title, initials, surname, action, response, review_type FROM (review_comments, users) LEFT JOIN properties ON review_comments.q_paper=properties.property_id WHERE review_comments.reviewer=users.id AND q_id=? AND q_paper=? ORDER BY surname");
    $changes->bind_param('ii', $_GET['q_id'], $_GET['paperID']);
  } else {
    $changes = $mysqli->prepare("SELECT paper_title, review_comments.id, category, comment, reviewed, title, initials, surname, action, response, review_type FROM (review_comments, users) LEFT JOIN properties ON review_comments.q_paper=properties.property_id WHERE review_comments.reviewer=users.id AND q_id=? ORDER BY q_paper, surname");
    $changes->bind_param('i', $_GET['q_id']);
  }
  $changes->execute();
  $changes->bind_result($paper_title, $id, $category, $comment, $reviewed, $title, $initials, $surname, $action, $response, $review_type);
  while ($row = $changes->fetch()) {
    if ((!isset($_GET['paperID']) or $_GET['paperID'] == '') and $paper_title != $old_paper_title) {
      echo "<tr><td colspan=\"5\"><h1>$paper_title</h1></td></tr>";
    }
    $old_paper_title = $paper_title;
    $status = 'OK';
    $image = 'ok_comment.png';
    if ($category == 2) {
      $status = 'Minor';
      $image = 'minor_comment.png';
    } elseif ($category == 3) {
      $status = 'Major';
      $image = 'major_comment.png';
    }
    echo "<tr><td style=\"border-bottom: solid 1px #ECE9D8\"><img src=\"/artwork/$image\" width=\"16\" height=\"16\" alt=\"$status\" /></td><td style=\"border-bottom: solid 1px #ECE9D8\">$title $initials $surname<br /><span style=\"color:#808080\">$review_type</span></td>";
    if (trim($comment) == '') $comment = '<span style="color:#808080">No Comments</span>';
    echo "<td style=\"border-bottom: solid 1px #ECE9D8\">" . nl2br($comment) . "</td><td style=\"border-bottom: solid 1px #ECE9D8\"><input type=\"hidden\" name=\"commentid$comment_no\" value=\"$id\" />\n<select name=\"action$comment_no\">";
    $action_array = array('Not actioned','Read - disagree','Read - actioned');
    foreach ($action_array as $individual_action) {
      if ($individual_action == $action) {
        echo "<option value=\"$individual_action\" selected>$individual_action</option>\n";
      } else {
        echo "<option value=\"$individual_action\">$individual_action</option>\n";
      }
    }
    
    echo "</select></td><td style=\"border-bottom: solid 1px #ECE9D8\"><textarea style=\"width:99%\" rows=\"3\" name=\"response$comment_no\">$response</textarea></td></tr>\n";
    $comment_no++;
  }
  $changes->close();
  
  echo "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"5\" style=\"text-align: center; width:100%\">";
  if ($disabled == '') {
    echo '<input type="submit" name="submit" value="Save Changes" />&nbsp;';
  } else {
    echo '<input type="submit" name="submit" value="Limited Save" />&nbsp;';
  }  
  echo "&nbsp;<input style=\"width:120px\" type=\"submit\" name=\"submit\" value=\"Cancel\" onclick=\"formCancel();\"/><input type=\"hidden\" name=\"mcqconvert\" id=\"mcqconvert\" value=\"0\" /></td></tr>\n";
  echo "</td></tr>\n";
  echo "</table>\n</td></tr>\n</table>";  
  echo "<input type=\"hidden\" name=\"comment_no\" value=\"$comment_no\" />\n";
?>
