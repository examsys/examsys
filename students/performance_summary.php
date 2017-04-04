<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Displays an overview of summative and offline reports for a student
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/errors.php';
require_once '../include/demo_replace.inc';
require_once '../include/sort.inc';
require_once '../include/toprightmenu.inc';

if (isset($_GET['userID'])) {
  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
    if ($_GET['userID'] != '') {
      $userID = $_GET['userID'];
    } else {
      display_error($string['idmissing'], $string['idmissing_msg'], false, true, false);
    }
  } else {  // Student is trying to hack into another students userID on the URL.
    header("HTTP/1.0 404 Not Found");
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
} else {
  $userID = $userObject->get_user_ID();
}

if (!UserUtils::userid_exists($userID, $mysqli)) {   // Check for valid user ID.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

/**
 * Gets a list of papers for which feadback is available for.
 * @param int $userID - The ID of the user to display the plots for. Usually the current student user but could be a member of staff viewing a student.
 * @param object $db	- Mysqli database link.
 * @return array			- List of papers that the user has sat and have been released.
 */
function get_taken_papers($userID, $db) {
  $papers = array();

  $i = 0;
  
  // Query for Summative and Offline papers
  $result = $db->prepare("SELECT DISTINCT log_metadata.id, paperID, paper_title, paper_type, pass_mark, calendar_year, started, crypt_name, idfeedback_release, type FROM log_metadata, properties LEFT JOIN feedback_release ON properties.property_id = feedback_release.paper_id WHERE log_metadata.paperID = properties.property_id AND paper_type IN ('2', '5') AND userID = ? AND feedback_release.type = 'cohort_performance' ORDER BY calendar_year DESC");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->store_result();
  $result->bind_result($metadataID, $paperID, $paper_title, $paper_type, $pass_mark, $calendar_year, $started, $crypt_name, $idfeedback_release, $feedback_type);
  while ($result->fetch()) {
    $papers[$i]['metadataID']     = $metadataID;
    $papers[$i]['paperID']        = $paperID;
    $papers[$i]['paper_title']    = $paper_title;
    $papers[$i]['paper_type']     = $paper_type;
    $papers[$i]['calendar_year']  = $calendar_year;
    $papers[$i]['started']        = $started;
    $papers[$i]['crypt_name']     = $crypt_name;
    $papers[$i]['pass_mark']      = $pass_mark;
    $results_cache = new ResultsCache($db);
    $papers[$i]['stats']          = $results_cache->get_paper_cache($paperID);
    $papers[$i]['idfeedback_release'] = $idfeedback_release;
    $papers[$i]['feedback_type']	= $feedback_type;

    $i++;
  }
  $result->close();
  
  // Query for OSCE stations
  $result = $db->prepare("SELECT DISTINCT log4_overall.id, q_paper, paper_title, paper_type, pass_mark, calendar_year, started, crypt_name, idfeedback_release, type FROM log4_overall, properties LEFT JOIN feedback_release ON properties.property_id = feedback_release.paper_id WHERE log4_overall.q_paper = properties.property_id AND paper_type IN ('4') AND userID = ? AND feedback_release.type = 'cohort_performance' ORDER BY calendar_year DESC");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->store_result();
  $result->bind_result($metadataID, $paperID, $paper_title, $paper_type, $pass_mark, $calendar_year, $started, $crypt_name, $idfeedback_release, $feedback_type);
  while ($result->fetch()) {
    $papers[$i]['metadataID']     = $metadataID;
    $papers[$i]['paperID']        = $paperID;
    $papers[$i]['paper_title']    = $paper_title;
    $papers[$i]['paper_type']     = $paper_type;
    $papers[$i]['calendar_year']  = $calendar_year;
    $papers[$i]['started']        = $started;
    $papers[$i]['crypt_name']     = $crypt_name;
    $papers[$i]['pass_mark']      = $pass_mark;
    $results_cache = new ResultsCache($db);
    $papers[$i]['stats']          = $results_cache->get_paper_cache($paperID);
    $papers[$i]['idfeedback_release'] = $idfeedback_release;
    $papers[$i]['feedback_type']	= $feedback_type;

    $i++;
  }
  $result->close();
  
  $sortby = 'calendar_year';
  $ordering = 'desc';
  $papers = array_csort($papers, $sortby, $ordering);

  return $papers;
}

$papers = get_taken_papers($userID, $mysqli);

$results_cache = new ResultsCache($mysqli);
$marks = $results_cache->get_paper_marks_by_student($userID);

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['performsummary']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <link rel="stylesheet" type="text/css" href="../css/popup_menu.css" />
  <style>
    body {font-size: 90%}
    .indent {margin-left: 30px}
    .label {position: relative; left: 171px; padding: 0; margin: 0; width: 132px; height: 11px}
    .subsect_table {margin-left: 8px; margin-top: 20px; margin-bottom: 6px; font-size:90%}
    .key {position: relative; width: 300px; height: 173px; border: 2px solid #FCE699; z-index: 10; float: right; top: 30px; right: 10px; font-size: 75%; padding: 5px; line-height: 100%; background-color: #FFFFEE; color: #404040}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/popup_menu.js"></script>
<?php
if ($userObject->has_role('Staff')) {
	echo '<script type="text/javascript" src="../js/staff_help.js"></script>';
} else {
	echo '<script type="text/javascript" src="../js/student_help.js"></script>';
}
if (!$userObject->has_role('Student')) {  // Do not show JavaScript if a student
?>
  <script>
    function setVars (paper_type, crypt_name, paperID, metadataID) {
      $('#paper_type').val(paper_type);
      $('#crypt_name').val(crypt_name);
      $('#paperID').val(paperID);
      $('#metadataID').val(metadataID);
    }

    function viewScript() {
      $('#menudiv').hide();
      if ($('#metadataID').val() != '') {
        var winwidth = screen.width-80;
        var winheight = screen.height-80;
        window.open("../paper/finish.php?id=" + $('#crypt_name').val() + "&metadataID=" + $('#metadataID').val() + "&log_type=" + $('#paper_type').val() + "&percent=" + $('#percent').val() + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      }
    }
    
    function viewFeedback() {
      $('#menudiv').hide();
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("../mapping/user_feedback.php?id=" + $('#crypt_name').val() + "&userID=<?php echo $userID; ?>&metadataID=" + $('#metadataID').val() + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewPersonalCohort() {
      window.location.href="../reports/personal_cohort_performance.php?paperID=" + $('#paperID').val() + "&userID=<?php echo $userID; ?>";
    }
    
    function jumpToPaper() {
      window.opener.location.href="../paper/details.php?paperID=" + $('#paperID').val();
      self.close();
    }

    document.onmousedown = mouseSelect;
  </script>
<?php
}
?>
</head>

<body>
<?php
	echo draw_toprightmenu();
?>

<div class="key">
  <img src="../artwork/boxplot_key.png" width="170" height="173" alt="Key" />
  <div style="top:-175px" class="label"><?php echo $string['maximumscore']; ?></div>
  <div style="top:-163px" class="label"><?php echo $string['studentsposition']; ?></div>
  <div style="top:-154px" class="label"><?php echo $string['topquartile']; ?></div>
  <div style="top:-145px" class="label"><?php echo $string['median']; ?></div>
  <div style="top:-138px" class="label"><?php echo $string['lowerquartile']; ?></div>
  <div style="top:-128px" class="label"><?php echo $string['passmark']; ?></div>
  <div style="top:-118px" class="label"><?php echo $string['minimumscore']; ?></div>
  <div style="top:-111px" class="label"><?php echo $string['examname']; ?></div>
  <div style="top:-105px" class="label"><?php echo $string['studentsmark']; ?></div>
</div>

<?php
if (!$userObject->has_role('Student')) {  // Do not create popup menu if student
?>
<div id="menudiv" class="popupmenu" style="padding:5px; width:300px">
  <div class="popup_row" onclick="viewScript();">
    <div class="popup_icon"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title" id="item1"><?php echo $string['examscript'] ?></div>
  </div>
  
  <div class="popup_row" onclick="viewFeedback();">
    <div class="popup_icon"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" /></div>
    <div class="popup_title" id="item2"><?php echo $string['objectives'] ?></div>
  </div>
  
  <div class="popup_row" onclick="viewPersonalCohort();">
    <div class="popup_icon"><img src="../artwork/personal_cohort.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title" id="item3"><?php echo $string['personalcohortperformance'] ?></div>
  </div>
  
  <div class="popup_divider_row">
    <div class="popup_icon"></div>
    <div class="popup_title"><img src="../artwork/popup_divider.png" width="100%" height="3" alt="-" /></div>
  </div>
 
  <div class="popup_row" onclick="viewPersonalCohort();">
    <div class="popup_icon"></div>
    <div class="popup_title" id="item3"><?php echo $string['jumptopaper'] ?></div>
  </div>
</div>
<?php
}

$demo = is_demo($userObject);
$student_details = UserUtils::get_user_details($userID, $mysqli);
$name = demo_replace($student_details['title'], $demo) . ' ' . demo_replace($student_details['surname'], $demo) . ', ' . demo_replace($student_details['first_names'], $demo) . ' (' . demo_replace($student_details['student_id'], $demo) . ')';
?>


<div style="position:absolute; top:0px; left:0px; width:100%">
<?php
echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:90%\">\n";
echo "<tr><th><div style=\"padding-left:10px; font-size:200%; font-weight:bold\">" . $string['performsummary'] . "</div><div style=\"padding-left:10px; padding-bottom:6px\">$name</div></th>";
echo "<th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></th></tr>\n";
echo "</table>\n<div>";

$old_calendar_year = '';
$plots_output = 0;
$col = 0;

foreach ($papers as $paper) {
  $display_paper = true;
  
  if ($paper['stats']['max_mark'] == '') {
    $display_paper = false;
  }
  if ($userObject->has_role('Student') and $paper['feedback_type'] != 'cohort_performance') {
    $display_paper = false;
  }

  if ($display_paper) {
    if ($old_calendar_year != $paper['calendar_year']) {
      //echo '<a name="' . $paper['calendar_year'] . '"></a><table border="0" class="subsect"><tr><td><nobr>' . $paper['calendar_year'] . '</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table>';
      echo '<a name="' . $paper['calendar_year'] . '"></a><div class="subsect_table"><div class="subsect_title"><nobr>' . $paper['calendar_year'] . '</nobr></div><div class="subsect_hr"><hr noshade="noshade" /></div></div>';
      $col = 0;
    }
  
    if ($col == 8) {				// Put in line break after 8 box/whisker plots.
      echo '<br />';
      $col = 0;
    }
  
    $q1 = $paper['stats']['q1'];
    $q2 = $paper['stats']['q2'];
    $q3 = $paper['stats']['q3'];
    $min = $paper['stats']['min_percent'];
    $max = $paper['stats']['max_percent'];
    $pass_mark = $paper['pass_mark'];
    $mark = (isset($marks[$paper['paperID']])) ? $marks[$paper['paperID']] : '';
    $exam = $paper['paper_title'];
  
    if ($userObject->has_role('Student')) {
      $onclick = '';
    } else {
      $onclick = " onclick=\"popMenu(3, event); setVars(" . $paper['paper_type'] . ", '" . $paper['crypt_name'] . "', " . $paper['paperID'] . ", '" . $paper['metadataID'] . "')\"";
    }
    
    if ($mark != '') {  // Do not plot if there is no student mark.
      if ($col == 0) {
        echo "<img src=\"draw_boxplot.php?exam=$exam&part=1&q1=$q1&q2=$q2&q3=$q3&min=$min&max=$max&passmark=$pass_mark&mark=$mark&scale=1\" width=\"166\" height=\"265\"$onclick alt=\"" . $string['boxplot'] . "\" class=\"indent\" />";
      } else {
        echo "<img src=\"draw_boxplot.php?exam=$exam&part=1&q1=$q1&q2=$q2&q3=$q3&min=$min&max=$max&passmark=$pass_mark&mark=$mark&scale=0\" width=\"115\" height=\"265\"$onclick alt=\"" . $string['boxplot'] . "\" />";
      }
      
      $plots_output++;
      $col++;
    }
    $old_calendar_year = $paper['calendar_year'];
  }
}
if ($plots_output == 0) {
  echo "<div style=\"margin:10px\">" . $string['noresults'] . "</div>\n";
}

if (!$userObject->has_role('Student')) {  // Do not show hidden fields if a student
?>
<input type="hidden" id="crypt_name" />
<input type="hidden" id="paperID" />
<input type="hidden" id="metadataID" />
<input type="hidden" id="paper_type" />
<?php
}
?>
</div>
<br />

</div>
</body>
</html>
