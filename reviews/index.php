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
* This is a homepage for External Examiners to land on.
* It looks up and presents only papers that they have been
* selected to review and are in the future.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../config/index.inc';  // Get the logo

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <?php
    if ($userObject->has_role(array('External Examiner'))) {
  ?>
  <title><?php echo $string['externalexaminerarea']; ?></title>
  <?php
    } else {
  ?>
  <title><?php echo $string['papersforreview']; ?></title>
  <?php
    }
  ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <?php
    if (!$userObject->has_role(array('External Examiner'))) {
  ?>
  <script>
    function startPaper(paperID) {
        window.open("./start.php?id="+paperID+"&review=1","paper");
    }
  </script>
   <?php
    }
  ?>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="../css/external_examiner.css" />
  <style type="text/css">
    *, html {margin: 0; padding: 0}
    body {font-size:90%; background-color: #EAEAEA}
    p {line-height:150%; margin-bottom: 1em}
    h1 {line-height:150%; color:#4A74B9; font-size:160%; font-weight:normal; margin-left:-25px; padding-top: 10px}
    .datepad {padding-left: 30px}
    .indent {margin-top: 0; padding-left: 40px; padding-right: 10px; padding-bottom: 50px; background-color: white; border-bottom: 1px solid #C0C0C0}
    .oss {float:right; position:relative; top:-30px; border: 1px solid #C0C0C0; background-color:white; padding:4px; margin-right:10px; width: 460px; font-size: 80%; line-height:150%}
  </style>
</head>

<body>
<?php
require '../include/toprightmenu.inc';

echo draw_toprightmenu(1);

if ($userObject->has_role(array('External Examiner'))) {
?>
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div style="padding:6px 6px 6px 16px">
    <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />
    <div class="logo_lrg_txt">Rog&#333;</div>
    <div class="logo_small_txt"><?php echo $string['externalexamineraccess']; ?> (<?php echo $userObject->get_title() . ' ' . $userObject->get_initials() . ' ' . $userObject->get_surname(); ?>)</div>
  </div>
</div>
  
<div class="indent">
<h1><?php echo $string['preexamreviewpapers'] ?></h1>
<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg1']; ?></p>

<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg2']; ?></p>

<table cellpadding="0" cellspacing="2" border="0" style="margin-left:10px; font-size:90%">
<?php
  $start_of_day_ts = strtotime('midnight');

  $result = $mysqli->prepare("SELECT paper_type, paper_title, property_id, bidirectional, fullscreen, MAX(screen) AS max_screen, UNIX_TIMESTAMP(external_review_deadline) AS external_review_deadline, crypt_name FROM (properties, properties_reviewers, papers) WHERE properties.property_id = properties_reviewers.paperID AND deleted IS NULL AND (DATE_ADD(start_date, INTERVAL 1 WEEK) > NOW() OR start_date IS NULL) AND properties.property_id = papers.paper AND reviewerID = ? GROUP BY paper ORDER BY paper_title");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->store_result();
  $result->bind_result($paper_type, $paper_title, $property_id, $bidirectional, $fullscreen, $max_screen, $external_review_deadline, $crypt_name);
  while ($result->fetch()) {
    $reviewed = '';
    if ($fullscreen == '') $fullscreen = 0;
    $log_results = $mysqli->prepare("SELECT UNIX_TIMESTAMP(MAX(started)) AS started FROM review_metadata WHERE reviewerID = ? and paperID = ?");
    $log_results->bind_param('ii', $userObject->get_user_ID(), $property_id);
    $log_results->execute();
    $log_results->store_result();
    $log_results->bind_result($reviewed);
    $log_results->fetch();
    $log_results->close();
    
    $restartdate = '';
    $display_deadline = date($configObject->get('cfg_long_date_php'), $external_review_deadline);
    
    echo "<tr><td align=\"center\"><a href=\"../paper/user_index.php?id=$crypt_name\">" . Paper_utils::displayIcon($paper_type, $paper_title, '', '', '', '') . "</a></td>\n";
    echo "  <td><a href=\"../paper/user_index.php?id=$crypt_name\">$paper_title</a><br /><div style=\"color:#C00000\">" . $string['deadline'] . " ";
    if ($start_of_day_ts > $external_review_deadline) {
      printf($string['expired'], $configObject->get('cfg_company'));
    } else {
      if ($display_deadline == '00/00/0000') {
        echo $string['notset'];
      } else {
        echo $display_deadline;
      }
    }
    echo '</div>';
    if ($reviewed == '') {
      echo '<span style="color:white; background-color:#FF4040; padding-left:5px; padding-right:5px">' . $string['notreviewed'] . '</span>';
    } else {
      echo '<span style="color:#808080">' . sprintf($string['reviewed'], date($configObject->get('cfg_short_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $reviewed)) . '</span>';
    }
    echo "</td></tr>\n<tr><td colspan=\"2\" style=\"font-size:80%\">&nbsp;</td>\n</tr>\n";
  }

  if ($result->num_rows == 0) {
    echo "<tr><td colspan=\"2\"><p style=\"color:red\">" . $string['nopapersfound'] . "</p></td></tr>\n";
  }
  $result->close();
  
  echo "</table>\n";
  
  
  $released_papers = ReviewUtils::get_past_papers($userObject->get_user_ID(), $mysqli);
  echo '<h1>' . $string['postexamreviews'] . '</h1>';
  
  echo "<p style=\"margin-left:15px; margin-right:15px; text-align:justify\">" .  $string['msg3'] . "</p>\n";

  echo "<table style=\"margin-left:15px\">\n";
  foreach ($released_papers as $paperID=>$paper_details) {
    echo "<tr><td><a href=\"class_totals.php?id=" . $paper_details['crypt_name'] . "\"><img src=\"../artwork/summative_16.gif\" width=\"16\" height=\"16\" style=\"margin-right:5px\" />" . $paper_details['paper_title'] . "</a></td><td class=\"datepad\">" . $paper_details['start_date'] . "</td></tr>\n";
  }
  echo "</table>\n";
  echo "</div>\n";

  $mysqli->close();
  
} else {
?>
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
</div>
<div class="indent">
<h1><?php echo $string['papersforreview'] ?></h1>
<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg4']; ?></p>
<p style="margin-left:15px; margin-right:15px; text-align:justify"><?php echo $string['msg2']; ?></p>
<table cellpadding="0" cellspacing="2" border="0" style="margin-left:10px; font-size:90%">
<?php
  $internalreview = new internalreview($mysqli);
  $review_papers = $internalreview->get_review_papers($userObject->get_user_ID());
  foreach($review_papers as $review_paper) {    
    echo "<tr><td align=\"center\"><a href=\"#\" onclick=\"startPaper('" . $review_paper['crypt_name'] . "'); return false;\">" . Paper_utils::displayIcon($review_paper['type'], $review_paper['paper_title'], '', '', '', '') . "</a></td>\n";
    echo "<td><a href=\"#\" onclick=\"startPaper('" . $review_paper['crypt_name'] . "'); return false;\">" . $review_paper['paper_title'] . "</a><br /><div style=\"color:#C00000\">" . $string['deadline'] . " " . $review_paper['internal_review_deadline'] . "</div>";
    if ($review_paper['reviewed'] == '') {
      echo "<span style=\"color:white; background-color:#FF4040\">&nbsp;" . $string['notreviewed'] . "&nbsp;</span>";
    } else {
      echo "<span style=\"color:#808080\">" . sprintf($string['reviewed'], $review_paper['reviewed']) . "</span>";
    }
    echo "</td></tr>\n<tr><td colspan=\"2\" style=\"font-size:80%\">&nbsp;</td>\n</tr>\n";
  }
  if (count($review_papers) == 0) {
    echo "<tr><td colspan=\"2\"><p style=\"color:red\">" . $string['nopapersfound'] . "</p></td></tr>\n";
  }
  echo "</table>\n";
  echo "</div>\n";
}
?>
<br />

<table class="oss">
  <tr>
  <td><img src="../artwork/oss_logo.png" /></td>
  <td style="padding-left:16px"><?php echo sprintf($string['rogodetails'], $configObject->get('rogo_version')) ?></a> <strong><a href="https://bitbucket.org/rogoOOS/rog/wiki/Home">bitbucket.org/rogoOOS/rog/wiki/Home</a></strong></td>
  </tr>
</table>

<div style="margin-left:10px; font-size:90%; color:#3F3F3F"><?php printf($string['copyrightmsg'], $configObject->get('cfg_company')); ?></div>

</body>
</html>