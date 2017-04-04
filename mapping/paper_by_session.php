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
require '../include/question_types.php';
require '../include/mapping.inc';
require '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper_title = $propertyObj->get_paper_title();
$session     = $propertyObj->get_calendar_year();
$start_date  = $propertyObj->get_raw_start_date();
$end_date    = $propertyObj->get_raw_end_date();
$paper_type  = $propertyObj->get_paper_type();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['mappingbysession'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/mapping.css" />
  <link rel="stylesheet" type="text/css" href="../css/tabs.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />

  <script src="../js/jquery-1.11.1.min.js" type="text/javascript"></script>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $('a[rel=external]').attr('target', '_blank');
    });
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

  $old_p_id = 0;
  $row_no = 0;
  $info_count = 0;
  $temp_array = array();
  $questionID_list = '';

  $result = $mysqli->prepare("SELECT random_mark, total_mark, p_id, q_id, q_type, screen, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited, display_pos FROM (properties, papers, questions) WHERE property_id=? AND paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos");
  $result->bind_param('ii', $paperID, $paperID);
  $result->execute();
  $result->bind_result($random_mark, $total_mark, $p_id, $q_id, $q_type, $screen, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited, $display_pos);
  while ($result->fetch()) {
    $row_no++;
    $temp_array[$q_id]['screen'] = $screen;
    $temp_array[$q_id]['q_type'] = $q_type;
    $temp_array[$q_id]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
    $temp_array[$q_id]['p_id'] = $p_id;
    $temp_array[$q_id]['q_id'] = $q_id;
    $temp_array[$q_id]['display_last_edited'] = $display_last_edited;
    $temp_array[$q_id]['q_media'] = $q_media;
    $temp_array[$q_id]['q_media_width'] = $q_media_width;
    $temp_array[$q_id]['q_media_height'] = $q_media_height;
    $temp_array[$q_id]['display_pos'] = $display_pos;

    $temp_array[$q_id]['qnumber'] = $display_pos - $info_count;

    if($q_type == 'info') $info_count++;

    $total_random_mark = $random_mark;
    $total_marks = $total_mark;
    $temp_total_marks = $total_mark;
    $questionID_list .= $q_id . ',';
  }
  $result->close();

  ?>
  <table class="header">
  <tr><th style="padding-top:1px">
  <table cellpadding="0" cellspacing="0" border="0" style="font-size:90%; width:378px">
  <td class="tabon"><?php echo $string['bysession']; ?></td>
  <td class="taboff" onclick="window.location.href='paper_by_question.php?paperID=<?php echo $paperID; ?>&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>'"><?php echo $string['byquestion']; ?></td>
  <td class="taboff" onclick="window.location.href='paper_by_year.php?paperID=<?php echo $paperID; ?>&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>'"><?php echo $string['longitudinal']; ?></td>
  </table>
  </th><th style="width:100%; text-align:right">&nbsp;</th>
  </tr>
  <tr><td colspan="2" style="background-color:#1E3C7B">&nbsp;</td></tr>
  <?php
  $questionID_list = substr($questionID_list,0,-1);
  $total_random_mark = 0;
  $total_marks = 0;
  if ($row_no > 0) {
		$tmp_match = Paper_utils::academic_year_from_title($paper_title);
		
		if ($tmp_match !== false and $tmp_match != $session) {
			echo "<tr><td colspan=\"4\" style=\"padding: 0\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%; font-size:100%\">\n";
			echo "<tr><td class=\"redwarn\" style=\"width:40px\"><img src=\"../artwork/exclamation_red_bg.png\" width=\"32\" height=\"32\" alt=\"Warning\" style=\"margin-bottom:-1px\" /></td><td colspan=\"7\" class=\"redwarn\"><strong>" . $string['warning'] . "</strong>&nbsp;&nbsp;";
			printf($string['nomatchsession'], $tmp_match, $session);
			echo "</td></tr>\n</table>\n</td></tr>\n";
		}
    $ul_start = false;
    $moduleIDs = Paper_utils::get_modules($paperID, $mysqli);
    $objsBySession = getObjectives($moduleIDs, $session, $paperID, $questionID_list, $mysqli);
    if ($objsBySession == 'error') {
      ?>
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
        <tr>
          <td class="redwarn" style="width:40px; line-height:0"><img src="../artwork/exclamation_red_bg.png" width="32" height="32" alt="Warning" /></td>
          <td class="redwarn">Error connecting to the curriculum mapping system.</td>
        </tr>
      </table>
      </body>
      </html>
      <?php
      exit();
    }
    unset($objsBySession['none_of_the_above']);
    ?>
    <tr>
    <td style="padding:0px">
    <?php
    foreach($objsBySession as $module => $sessions ) {
      if (count($objsBySession) > 1) {
        echo "<tr><td><h1>$module " . $string['objectives'] . "</h1></td></tr>";
      }
      foreach($sessions as $identifier => $sessionData) {
        if ($ul_start) {
          echo '</ul>';
        }
        echo "<tr><td colspan=\"2\" style=\"padding-left:4px\"><table border=\"0\" style=\"padding-top:6px; padding-bottom:2px; width:100%; color:#1E3287\"><tr><td><nobr>";
        if ($sessionData['class_code'] != '') {
          echo $sessionData['class_code'] . ': ';
        }
        echo $sessionData['title'] . ' <a href="' . $sessionData['source_url'] . '" rel="external"><img src="../artwork/small_link.png" width="11" height="11" alt="" /></a> ';

        echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n</td></tr>\n";
        if (isset($sessionData["objectives"]) and is_array($sessionData["objectives"])) {
          echo '<tr><td colspan="2"><ul>';
          foreach ($sessionData["objectives"] as $id => $objectives) {
            if (is_array($objectives['mapped'])) {
              echo '<li class="mapped">' . strip_tags($objectives['content'], '<b><i><strong><em><sub><sup>') . ' <span class="mapping">';
              $i = 0;
              foreach ($objectives['mapped'] as $q_id) {
								if ($exclusions->get_exclusions_by_qid($q_id) != '0000000000000000000000000000000000000000') {
                  $class = 'q_excluded';
                } else {
                  $class = 'q_ok';
                }
                if ($i != 0) echo ', ';
                $i++;
                echo "<a class=\"$class\" href=\"../question/view_question.php?q_id=" . $q_id . "&qNo=" . $temp_array[$q_id]['qnumber'] . "\" target=\"_blank\">Q" . $temp_array[$q_id]['qnumber'] . "</a>";
              }
              echo'</span></li>';
            } else {
              // Could display unmapped objective here!
              echo '<li class="unmapped">' . strip_tags($objectives['content'], '<b><i><strong><em><sub><sup>') . '</li>';
            }
          }
          echo '</ul></td></tr>';
        }
      }

    }
    if ($ul_start) {
      echo '</ul>';
    }
    echo "</td></tr>\n</table>";
  }
  $mysqli->close();
?>
</table>
</div>

</body>
</html>
