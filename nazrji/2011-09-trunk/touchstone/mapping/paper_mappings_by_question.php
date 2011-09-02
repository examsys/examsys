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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/mapping.inc';
  $paperID = $_GET['paperID'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: <?php echo $string['mappingbyquestion'] . " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style style="text/css">
  td {font-size:100%}
  img {border:none}
  .q_no {text-align:right; vertical-align:top; cursor:pointer; width:40px}
  .divider {font-family:Arial,sans-serif; font-size:90%; font-weight:bold}
  .mapping {font-size:90%;color:#FF6300;font-weight:normal}
  .mapping_exclueded {color:red;font-weight:normal;text-decoration:line-through;}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="JavaScript">
  function mapQuestion(qNo, pid, qid, session) {
    mapWindow = window.open('./map_question.php?qNo=' + qNo + '&paperID=' + pid + '&q_id=' + qid + '&session=' + session, "",'height=' + (screen.height - 300) + ',width=' + (screen.width - 300) + ',scrollbars=1,resizable=1,statusbar=0');
    mapWindow.moveTo(100,100);
  }
</script>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/paper_options.inc';
?>

<div id="content" class="content">
<?php
  
  if (!isset($_GET['ordering'])) {
    $ordering = 'screen';
    $direction = 'asc';
  }

  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }
    
  $result = $mysqli->prepare("SELECT paper_title, moduleID, calendar_year, start_date, end_date, paper_type FROM properties WHERE property_id=? LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title, $moduleID, $session, $start_date, $end_date, $paper_type);
  while ($row = $result->fetch()) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:80%\">\n";
    echo '<tr><td style="background-color:#F1F5FB">';
    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if ($folder != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
    }
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';
    echo "<div style=\"font-size:220%; font-weight:bold; margin-left:10px\">" . $string['mappedobjectives'] . "</div></td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(147); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n</table>\n";
  }
  $result->close();


  //build excluded array
  // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($row = $result->fetch()) {
    $excluded[$q_id] = $parts;
  }
  $result->close();
  
  ?>
    <table cellpadding="0" cellspacing="0" border="0" style="display:block; font-size:90%; background-color:white">
    <tr><td>
    <table cellpadding="0" cellspacing="0" border="0" style="font-size:90%; width:378px; background-color:#F1F5FB">
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)" onclick="window.location.href='paper_mappings_by_session.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['bysession']; ?></td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_on.gif)"><?php echo $string['byquestion']; ?></td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)" onclick="window.location.href='paper_mappings_by_year.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['longitudinal']; ?></td>
    </table>
    </td><td style="width:100%; background-color:#F1F5FB; text-align:right">&nbsp;</td>
    </tr>
    <tr><td colspan="5" style="background-color:#1E3C7B">&nbsp;</td></tr>
    </table>
    <?php
      $year_in_title = false;
      $tmp_match = '';
      if (preg_match( '/\d\d\d\d.\d\d\d\d/' , $paper_title , $matches) == 1) {
        $year_in_title = true;
        $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
      } elseif (preg_match( '/\d\d\d\d.\d\d/' , $paper_title , $matches) == 1) {
        $year_in_title = true;
        $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
      } elseif (preg_match( '/\d\d.\d\d/' , $paper_title , $matches) == 1) {
        $year_in_title = true;      
        $tmp_match = '20' . substr($matches[0],0,2) . '/' . substr($matches[0],-2);
      }
      if ($year_in_title == true) {
        if ($tmp_match != $session) {
          echo "<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" style=\"width:100%; font-size:80%\">\n";
          echo "<tr><td style=\"width:40px; height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/non_owner_icon.png\" width=\"25\" height=\"30\" alt=\"Warning\" />&nbsp;&nbsp;</td><td colspan=\"7\" style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><strong>" . $string['warning'] . "</strong>&nbsp;&nbsp;&nbsp;";
          printf($string['nomatchsession'], $tmp_match, $session);
          echo "</td></tr>\n</table>\n";
        }
      }
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"  style=\"width:100%; font-size:80%; background-color:white\">\n";
  $old_p_id = 0;
  $row_no = 0;
  $temp_array = array();

  $result = $mysqli->prepare("SELECT random_mark, total_mark, paper_ownerID, q_group, ownerID, p_id, q_id, q_type, screen, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited, display_pos FROM (properties, papers, questions) WHERE property_id=? AND paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos");
  $result->bind_param('ii', $paperID, $paperID);
  $result->execute();
  $result->bind_result($total_random_mark, $total_marks, $paper_ownerID, $q_group, $ownerID, $p_id, $q_id, $q_type, $screen, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited, $display_pos);
  while ($row = $result->fetch()) {
    $row_no++;
    $temp_array[$row_no]['screen'] = $screen;
    $temp_array[$row_no]['q_type'] = $q_type;
    $temp_array[$row_no]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
    if (strlen($temp_array[$row_no]['leadin']) > 160) $temp_array[$row_no]['leadin'] = substr($temp_array[$row_no]['leadin'],0,160) . "...";
    $temp_array[$row_no]['p_id'] = $p_id;
    $temp_array[$row_no]['q_id'] = $q_id;
    $temp_array[$row_no]['display_last_edited'] = $display_last_edited;
    $temp_array[$row_no]['q_media'] = $q_media;
    $temp_array[$row_no]['q_media_width'] = $q_media_width;
    $temp_array[$row_no]['q_media_height'] = $q_media_height;
    $temp_array[$row_no]['ownerID'] = $ownerID;
    $temp_array[$row_no]['display_pos'] = $display_pos;
    $temp_array[$row_no]['q_group'] = $q_group;
    $temp_total_marks = $total_marks;
  }
  $result->close();

  $total_random_mark = 0;
  $total_marks = 0;
  $correct_no = 0;
  if ($row_no > 0) {
    $old_q_id = 0;
    $old_score_method = '';
    $old_marks = 0;
    $row_no2 = 1;
    $stems = 0;
    $result = $mysqli->prepare("SELECT q_type, q_id, correct, score_method, q_media_height, q_media_width, option_text FROM (papers, questions, options) WHERE papers.paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, o_id");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($q_type, $q_id, $correct, $score_method, $q_media_height, $q_media_width, $option_text);
    while ($row = $result->fetch()) {
      if ($old_q_id != $q_id and $old_q_id != 0) {
        $old_marks = $total_marks;
        $temp_array[$row_no2]['marks'] = $total_marks - $old_marks;
        $stems = 0;
        $correct_no = 0;
        $row_no2++;
      }
      $old_q_id = $q_id;
      $old_q_type = $q_type;
      $old_score_method = $score_method;
      $old_correct = $correct;
      $old_q_media_width = $q_media_width;
      $old_q_media_height = $q_media_height;
      $old_option_text = $option_text;
      if ($q_type == 'mrq') {
        if ($correct == 'y') $correct_no++;
      }
      if ($q_type == 'rank') {
        if ($correct > 0) $correct_no++;
      }
      $stems++;
    }
    $result->close();
    $old_marks = $total_marks;
    $temp_array[$row_no2]['marks'] = $total_marks - $old_marks;
  }

  $old_screen = 0;
  $question_number = 0;
  for ($x=1; $x<=$row_no; $x++) {
    if ($old_screen != $temp_array[$x]['screen']) {
      if ($old_screen < ($temp_array[$x]['screen'] - 1)) {
        for ($missing=1; $missing<($temp_array[$x]['screen'] - $old_screen); $missing++) {
          echo '<tr><td colspan="3" style="height:10px"></td></tr>';
          echo '<tr><td></td><td colspan="3" class="divider">Screen ' . ($old_screen + $missing) . '</td></tr>';
          echo '<tr><td colspan="3" style="height:5px"><img src="../artwork/divider_bar.gif" width="290" height="1" /></td></tr>';
          echo '<tr><td colspan="3" style="background-color:#FFC0C0; padding:5px"><strong>Warning:</strong> there are no questions on this screen.<br />This will produce an error if the paper is tested!</td></tr>';
        }
      }
      echo "<tr><td colspan=\"4\" style=\"padding-left:4px\"><table border=\"0\" style=\"padding-top:6px; padding-bottom:2px; width:100%; color:#1E3287\"><tr><td><nobr>Screen " . $temp_array[$x]['screen'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n</td></tr>\n";
    }
    $old_screen = $temp_array[$x]['screen'];
    
    $objByModule = getObjectivesByMapping($moduleID,$session,$paperID,$temp_array[$x]['q_id'],$mysqli);
    if(array_key_exists($temp_array[$x]['q_id'],$excluded)) {
      $class = 'mapping_exclueded';
    } else {
      $class = '';
    }
    echo "<tr>";

    if (count($objByModule) > 0 or $temp_array[$x]['q_type'] == 'info') {
      echo '<td style="width:16px">&nbsp;</td>';
    } else {
      echo '<td style="width:16px"><img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="No Mappings" /></td>';
    }

    if ($temp_array[$x]['q_type'] == 'info') {
      echo '<td class="q_no"><img src="../artwork/black_white_info_icon.png" width="6" height="12" alt="Info" />&nbsp;&nbsp;</td>';
    } else {
      $question_number++;
      echo "<td class=\"q_no\">&nbsp;$question_number.&nbsp;</td>";
    }
    if ($temp_array[$x]['leadin'] != '') {
      if (count($objByModule) > 0 or $temp_array[$x]['q_type'] == 'info') {
        echo '<td class="' . $class . '" valign="middle" style="width:100%">';
      } else {
        echo '<td class="' . $class . '" valign="middle" style="color:#C00000; width:100%">';
      }
      echo $temp_array[$x]['leadin'] . "&nbsp;&nbsp;";
      if($temp_array[$x]['q_type'] != 'info')
        echo "<img style=\"cursor: pointer\" onclick=\"mapQuestion('$question_number', '" . $paperID . "','" . $temp_array[$x]['q_id'] . "','" . $session . "')\" src=\"../artwork/map_question.gif\" width=\"16\" height=\"14\"/></td>";
    } elseif (strpos($temp_array[$x]['q_media'],'.swf') !== false) {
      echo "<td><img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" /></td>";
      if($temp_array[$x]['q_type'] != 'info')
        echo "<img style=\"cursor: pointer\" onclick=\"mapQuestion('$question_number', '" . $paperID . "','" . $temp_array[$x]['q_id'] . "','" . $session . "')\" src=\"../artwork/map_question.gif\" width=\"16\" height=\"14\"/></td>";
    } else {
      echo "<td><img src=\"../media/" . $temp_array[$x]['q_media'] . "\" width=\"" . ($temp_array[$x]['q_media_width'] / 3) . "\" height=\"" . ($temp_array[$x]['q_media_height'] /3) . "\" alt=\"Media file\" border=\"1\" />";
      if($temp_array[$x]['q_type'] != 'info')
        echo "<img style=\"cursor: pointer\" onclick=\"mapQuestion('$question_number', '" . $paperID . "','" . $temp_array[$x]['q_id'] . "','" . $session . "')\" src=\"../artwork/map_question.gif\" width=\"16\" height=\"14\"/></td>";

    }
    echo "</tr>\n";

    //output mappings
    echo "<tr><td colspan=\"2\">&nbsp;</td><td>\n";
    $sessiontitle = '';
    if(count($objByModule) > 0) {
      if(isset($objByModule['none_of_the_above']['mapped']) AND $objByModule['none_of_the_above']['mapped'] == 1) {
        echo "<ul class=\"$class\" style=\"list-style-type:none; margin-left:10px; padding:0px\">\n<li style=\"padding-left:10px; color:red; background-image:url(../artwork/small_warning_16.png); background-repeat:no-repeat\"><strong>Warning:</strong> This question does not map to the module!</li></ul>\n";
      } else {
        echo "<ul class=\"$class\" style=\"list-style-type:disc; margin-left:20px; margin-top:5px\">\n";
        foreach($objByModule as $module => $mappings) {
          foreach($mappings as $id => $mappingData) {
            if( $mappingData['session']['class_code'] != '') {
              $sessiondata = $mappingData['session']['class_code'];
              $sessiontitle = $mappingData['session']['title'];
              $sessiontitle .= ' ' . $mappingData['session']['occurrance'];
            } else {
              $sessiondata = $mappingData['session']['title'];
            }
            echo "<li>";
            if(count($objByModule) > 1) {
              echo "$module: ";
            }
              echo $mappingData['content'];
              echo "&nbsp;&nbsp;&nbsp;<span title=\"$sessiontitle\" class=\"mapping\"><a href=\"" . $mappingData['session']['source_url'] . "\" target=\"_blank\"><img src=\"../artwork/small_link.png\" width=\"12\" height=\"12\" /></a>&nbsp;<a href=\"" . $mappingData['session']['source_url'] . "\" target=\"_blank\">" . $sessiondata ."</a></span>";
              echo "</li>";
            }
        }
      }
      echo "</ul>\n";
    }
    echo "<tr></td>\n";
    echo "<tr><td colspan=\"5\" style=\"height:3px\"></td></tr>\n";
  }
  $mysqli->close();
?>
</table>
</div>
</body>
</html>
