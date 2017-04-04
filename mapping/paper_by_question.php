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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/mapping.inc';
require '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper_title = $propertyObj->get_paper_title();
$session = $propertyObj->get_calendar_year();
$start_date = $propertyObj->get_raw_start_date();
$end_date = $propertyObj->get_raw_end_date();
$paper_type = $propertyObj->get_paper_type();
$mediadirectory = rogo_directory::get_directory('media');
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['mappingbyquestion'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/mapping.css" />
  <link rel="stylesheet" type="text/css" href="../css/tabs.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $('a[rel=external]').attr('target', '_blank');
    });

    function mapQuestion(qNo, pid, qid, session) {
      mapWindow = window.open('./map_question.php?qNo=' + qNo + '&paperID=' + pid + '&q_id=' + qid + '&session=' + session, "",'height=' + (screen.height - 300) + ',width=' + (screen.width - 300) + ',scrollbars=yes,resizable=yes,statusbar=no');
      mapWindow.moveTo(100,100);
    }
  </script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(147);
?>
<div id="content">
<?php
  if (!isset($_GET['ordering'])) {
    $ordering = 'screen';
    $direction = 'asc';
  }

  echo "<div class=\"head_title\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    $modules = explode(',', $_GET['module']);
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $modules[0] . '">' . module_utils::get_moduleid_from_id($modules[0], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a></div>';
  echo "<div class=\"page_title\">" . $string['mappedobjectives'] . "</div>\n</div>\n";

  // Get any questions to exclude.
	$exclusions = new Exclusion($paperID, $mysqli);
	$exclusions->load();

  ?>
  <table class="header">
  <tr><th style="padding-top:1px">
  <table cellpadding="0" cellspacing="0" border="0" style="font-size:90%; width:378px">
  <td class="taboff" onclick="window.location.href='paper_by_session.php?paperID=<?php echo $paperID; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['bysession']; ?></td>
  <td class="tabon"><?php echo $string['byquestion']; ?></td>
  <td class="taboff" onclick="window.location.href='paper_by_year.php?paperID=<?php echo $paperID; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['longitudinal']; ?></td>
  </table>
  </th><th style="width:100%; text-align:right">&nbsp;</th>
  </tr>
  <tr><td colspan="5" style="background-color:#1E3C7B">&nbsp;</td></tr>
  </table>
  <?php
	$tmp_match = Paper_utils::academic_year_from_title($paper_title);

	if ($tmp_match !== false and $tmp_match != $session) {
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size:90%; width:100%; font-size:100%\">\n";
		echo "<tr><td class=\"redwarn\" style=\"width:40px\"><img src=\"../artwork/exclamation_red_bg.png\" width=\"32\" height=\"32\" alt=\"Warning\" style=\"margin-bottom:-1px\" /></td><td colspan=\"7\" class=\"redwarn\"><strong>" . $string['warning'] . "</strong>&nbsp;&nbsp;";
		printf($string['nomatchsession'], $tmp_match, $session);
		echo "</td></tr>\n</table>\n";
	}
  
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"  style=\"width:100%\">\n";
  $old_p_id = 0;
  $row_no = 0;
  $temp_array = array();

  $result = $mysqli->prepare("SELECT random_mark, total_mark, paper_ownerID, ownerID, p_id, q_id, q_type, screen, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited, display_pos FROM (properties, papers, questions) WHERE property_id=? AND paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos");
  $result->bind_param('ii', $paperID, $paperID);
  $result->execute();
  $result->bind_result($total_random_mark, $total_marks, $paper_ownerID, $ownerID, $p_id, $q_id, $q_type, $screen, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited, $display_pos);
  while ($result->fetch()) {
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
    while ($result->fetch()) {
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
          echo '<tr><td colspan="3" style="background-color:#FFC0C0; padding:5px"><strong>' . $string['warning'] . ':</strong> ' . $string['noquestiononscreen'] . '</td></tr>';
        }
      }
      echo "<tr><td colspan=\"4\" style=\"padding-left:4px\"><table border=\"0\" style=\"padding-top:6px; padding-bottom:2px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['screen'] . " " . $temp_array[$x]['screen'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n</td></tr>\n";
    }
    $old_screen = $temp_array[$x]['screen'];

    $moduleIDs = Paper_utils::get_modules($paperID, $mysqli);
    $objByModule = getObjectivesByMapping($moduleIDs, $session, $paperID, $temp_array[$x]['q_id'], $mysqli);
    if ($exclusions->get_exclusions_by_qid($temp_array[$x]['q_id']) != '0000000000000000000000000000000000000000') {
      $class = 'mapping_exclueded';
    } else {
      $class = '';
    }
    echo "<tr>";

    if (count($objByModule) > 0 or $temp_array[$x]['q_type'] == 'info') {
      echo '<td style="width:16px">&nbsp;</td>';
    } else {
      echo '<td style="width:16px"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="No Mappings" /></td>';
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
      if ($temp_array[$x]['q_type'] != 'info') {
        echo "<img style=\"cursor: pointer\" onclick=\"mapQuestion('$question_number', '" . $paperID . "','" . $temp_array[$x]['q_id'] . "','" . $session . "')\" src=\"../artwork/map_question.gif\" width=\"16\" height=\"14\"/></td>";
      } elseif (strpos($temp_array[$x]['q_media'],'.swf') !== false) {
        echo "<td><img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" /></td>";
        if ($temp_array[$x]['q_type'] != 'info') echo "<img style=\"cursor: pointer\" onclick=\"mapQuestion('$question_number', '" . $paperID . "','" . $temp_array[$x]['q_id'] . "','" . $session . "')\" src=\"../artwork/map_question.gif\" width=\"16\" height=\"14\"/></td>";
      } else {
        echo "<td><img src=\"" . $mediadirectory->url($temp_array[$x]['q_media']) . "\" width=\"" . ($temp_array[$x]['q_media_width'] / 3) . "\" height=\"" . ($temp_array[$x]['q_media_height'] /3) . "\" alt=\"Media file\" border=\"1\" />";
        if ($temp_array[$x]['q_type'] != 'info') echo "<img style=\"cursor: pointer\" onclick=\"mapQuestion('$question_number', '" . $paperID . "','" . $temp_array[$x]['q_id'] . "','" . $session . "')\" src=\"../artwork/map_question.gif\" width=\"16\" height=\"14\"/></td>";
      }
      echo "</tr>\n";

      //output mappings
      echo "<tr><td colspan=\"2\">&nbsp;</td><td>\n";
      $sessiontitle = '';
      if (count($objByModule) > 0) {
        if (isset($objByModule['none_of_the_above']['mapped']) and $objByModule['none_of_the_above']['mapped'] == 1) {
          echo "<ul class=\"$class\" style=\"list-style-type:none; margin-left:10px; padding:0px\">\n<li style=\"padding-left:10px; color:red; background-image:url(../artwork/small_yellow_warning_icon.gif); background-repeat:no-repeat\">&nbsp;" . $string['questiononnotmap'] . "</li></ul>\n";
        } else {
          echo "<ul class=\"$class\" style=\"list-style-type:disc; margin-top:5px\">\n";
          foreach ($objByModule as $module => $mappings) {
            foreach ($mappings as $id => $mappingData) {
              if( $mappingData['session']['class_code'] != '') {
                $sessiondata = $mappingData['session']['class_code'];
                $sessiontitle = $mappingData['session']['title'];
                $sessiontitle .= ' ' . $mappingData['session']['occurrance'];
              } else {
                $sessiondata = $mappingData['session']['title'];
              }
              echo '<li>';
              if (count($objByModule) > 1) {
                echo "$module: ";
              }
              echo strip_tags($mappingData['content'], '<b><i><strong><em><sub><sup>');
              echo "&nbsp;&nbsp;&nbsp;<span title=\"$sessiontitle\" class=\"mapping\"><a href=\"" . $mappingData['session']['source_url'] . "\" rel=\"external\"><img src=\"../artwork/small_link.png\" width=\"11\" height=\"11\" /></a>&nbsp;<a href=\"" . $mappingData['session']['source_url'] . "\" rel=\"external\">" . $sessiondata ."</a></span>";
              echo '</li>';
            }
          }
        }
        echo "</ul>\n";
      }
      echo "<tr></td>\n";
      echo "<tr><td colspan=\"5\" style=\"height:3px\"></td></tr>\n";
    }
  }
  $mysqli->close();
?>
</table>
</div>
</body>
</html>
