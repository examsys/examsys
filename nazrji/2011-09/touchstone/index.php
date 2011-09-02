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
* TouchStone hompage. Uses ./include/options_menu.inc for the sidebar menu.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require './include/staff_student_auth.inc';
  require './include/sidebar_menu.inc';
  require './config/index.inc';

  // Redirect Students (if not also staff), External Examiners and Invigilators to their own areas.
  if(strpos($userroles,'Student') !== false and strpos($userroles,'Staff') === false and strpos($userroles,'Admin') === false and strpos($userroles,'SysAdmin') === false) {
    header("location: " . $protocol. $_SERVER['HTTP_HOST'] . "/touchstone/paper/available.php");
    exit;
  } elseif ($userroles == 'External Examiner') {
    header("location: " . $protocol. $_SERVER['HTTP_HOST'] . "/touchstone/reviews/");
    exit;
  } elseif ($userroles == 'Invigilator') {
    header("location: " . $protocol. $_SERVER['HTTP_HOST'] . "/touchstone/invigilator/");
    exit;
  }

// If we're still here we should be staff
require './include/staff_auth.inc';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>TouchStone<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="./css/submenu.css" />

<script src="./javascript/staff_help.js" type="text/javascript"></script>
<script src="./javascript/sidebar.js" type="text/javascript"></script>
<script language="JavaScript">
  function illegalChar(codeID) {
    if (codeID == 38) {
      alert("Character '&' illegal - please use alternative characters in folder name.");
    } else if (codeID == 59) {
      alert("Character ';' illegal - please use alternative characters in folder name.");
    } else if (codeID == 63) {
      alert("Character '?' illegal - please use alternative characters in folder name.");
    } else if (codeID == 64) {
      alert("Character '@' illegal - please use alternative characters in folder name.");
    } else if (codeID == 94) {
      alert("Character '^' illegal - please use alternative characters in folder name.");
    } else if (codeID == 126) {
      alert("Character '~' illegal - please use alternative characters in folder name.");
    }
    event.returnValue = false;
  }

  function newPaper(paperID) {
    notice = window.open("./paper/new_paper1.php?folder=","properties","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function displayCredits(){
    notice=window.open("./credits/credits.php","credits","width=700,height=487,scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=0,menubar=0");
    notice.moveTo(screen.width/2-350,screen.height/2-243)
    if (window.focus) {
      notice.focus();
    }
  }
</script>
</head>

<body onclick="hideMenus()">

<?php
  require './include/options_menu.inc';
?>

<div id="content" class="content" style="font-size:80%">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<?php
  // -- Create new folder ---------------------------------------------------
  $duplicate_name = 0;
  if (isset($_POST['submit'])) {
    $new_folder_name = $_POST['folder_name'];
    $folder_details = $mysqli->query("SELECT name FROM folders WHERE ownerID=$userID");
    while ($folder_row = $folder_details->fetch_assoc()) {
      if ($folder_row['name'] == $new_folder_name) $duplicate_name = 1;
    }
    $folder_details->close();

    if ($duplicate_name == 0) {
      if ($folder_query = $mysqli->prepare("INSERT INTO folders VALUES (NULL,$userID, ?,'',NOW(),'yellow',NULL)")) {
        $folder_query->bind_param('s', $new_folder_name);
        $folder_query->execute();
        $folder_query->close();
      } else {
        display_error("New Folder Error",$mysqli->error);
      }
    }
  }

  function displayIcon($paper_type,$title,$initials,$surname,$shared,$locked) {
    switch ($paper_type) {
      case 0:
        $html = "<img src=\"./artwork/formative" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Formative Self-Assessment&#013;Author: $title $initials $surname\" border=\"0\" />";
        break;
      case 1:
        $html = "<img src=\"./artwork/progress" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Progress Test&#013;Author: $title $initials $surname\" border=\"0\" />";
        break;
      case 2:
        $html = "<img src=\"./artwork/summative" . $shared . $locked . ".png\" width=\"48\" height=\"48\" alt=\"Type: Summative Exam&#013;Author: $title $initials $surname\" border=\"0\" />";
        break;
      case 3:
        $html = "<img src=\"./artwork/survey" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Survey&#013;Author: $title $initials $surname\" border=\"0\" />";
        break;
      case 4:
        $html = "<img src=\"./artwork/osce" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: OSCE Station&#013;Author: $title $initials $surname\" border=\"0\" />";
        break;
      case 5:
        $html = "<img src=\"./artwork/offline" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Offline Paper&#013;Author: $title $initials $surname\" border=\"0\" />";
        break;
    }
    return $html;
  }

  function displayPaperIcon($row) {
    global $folder;

    if (!isset($row['title'])) {
      $row['title'] = '';
    }
    if (!isset($row['initials'])) {
      $row['initials'] = '';
    }
    if (!isset($row['surname'])) {
      $row['surname'] = '';
    }

    echo '<div class="file">';
    echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:60px" align="center">';
    $icon_type = $row['paper_type'];
    if (date("YmdHis", time()) >= $row['start_date']) {
      $locked = '_locked';
    } else {
      $locked = '';
    }
    echo "<a href=\"./paper/details.php?paperID=" . $row['property_id'] . "&folder=$folder&module=\">" . displayIcon($icon_type,$row['title'],$row['initials'],$row['surname'],'',$locked) . "</a></td>\n";
    echo "</td><td><a href=\"./paper/details.php?paperID=" . $row['property_id'] . "&folder=$folder&module=\" class=\"blacklink\">" . $row['paper_title'] . '</a><br />';
    echo '  <span style="color:#808080">';
    if ($row['screens'] == NULL) {
      echo '0 Screens, ';
    } elseif ($row['screens'] == 1) {
      echo $row['screens'] . ' Screen';
    } else {
      echo $row['screens'] . ' Screens';
    }
    echo ', <span style="color:red">No modules set</span>';
    echo '<br />';
    echo '  ' . $row['display_start_date'];
    if ($icon_type == 2) {
      echo ', ' . $row['exam_duration'] . 'mins';
    } else {
      echo ' to ' . $row['display_end_date'];
    }
    echo "</td></tr></table></div>\n";
  }

  //Update the last log in date in users.
  $stmt = $mysqli->prepare("UPDATE users SET last_login=NOW() WHERE id=?");
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->close();
?>
<script language="JavaScript">
  function startPaper(paperID,fullsc) {
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    if (fullsc == 0) {
      window.open("./reviews/start.php?paperID="+paperID+"&review=1","paper","width="+winwidth+",height="+winheight+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    } else {
      window.open("./reviews/start.php?paperID="+paperID+"&review=1","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }
</script>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr>
    <td style="background-color:#F1F5FB"><div style="font-size:22pt; font-weight:bold">&nbsp;TouchStone<?php echo " $cfg_install_type"; ?></div><div style="position:relative; left:12px; top:-3px; font-size:8pt">Assessment Management System</div></td>
    <td style="background-color:#F1F5FB; text-align:right"><?php echo $logo_html; ?>&nbsp;&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" style="height:3px"><img src="./artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td>
  </tr>
</table>
<div style="padding-left:14px; padding-right:14px">
<?php
  if ($news_msg != '') {
    echo "<blockquote>\n<table cellpadding=\"10\" cellspacing=\"0\" border=\"0\"><tr><td style=\"border-top:1px solid #EEEEEE; border-bottom:1px solid #EEEEEE; border-left:1px solid #EEEEEE\"><img src=\"./artwork/news.png\" width=\"62\" height=\"52\" alt=\"Newspaper\" /></td><td style=\"border-top:1px solid #EEEEEE; border-bottom:1px solid #EEEEEE; border-right:1px solid #EEEEEE; border-left:1px solid #EEEEEE; vertical-align:top\">$news_msg</td></tr></table>\n</blockquote>\n";
  }

  echo "<br />\n";
  $icons = array('formative','progress','summative','survey','osce','offline');

  // -- Display top 10 recent papers ----------------------------------
  $query_string = "SELECT paperID, paper_title, moduleID, accessed, paper_type FROM (recent_papers, properties) WHERE userID=$userID AND recent_papers.paperID=properties.property_id ORDER BY accessed DESC LIMIT 10";
  $results = $mysqli->query($query_string) or die("failed : ".$mysqli->error." $query_string");

  echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>My Recent Papers (" . $results->num_rows . ")</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

  while ($row = $results->fetch_assoc()) {
    echo "<div style=\"padding-left:12px\"><a href=\"./paper/details.php?paperID=" . $row['paperID'] . "&folder=&module=" . $row['moduleID'] . "\"><img src=\"./artwork/" . $icons[$row['paper_type']] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $row['paper_type'] . "\" /></a>&nbsp;<a class=\"recent\"";
    if (strpos($row['paper_title'],'[deleted') !== false) echo ' style="color:#808080"';
    echo "href=\"./paper/details.php?paperID=" . $row['paperID'] . "&folder=&module=" . $row['moduleID'] . "\">" . $row['paper_title'] . "</a></div>\n";
  }
  $results->close();

  // -- Display any papers for review ---------------------------------
  $query_string = "SELECT paper_type, paper_title, property_id, bidirectional, fullscreen, MAX(screen) AS max_screen, DATE_FORMAT(internal_review_deadline,'%d/%m/%Y') AS internal_review_deadline FROM (properties, papers) WHERE deleted IS NULL AND internal_review_deadline >= NOW() AND properties.property_id=papers.paper AND internal_reviewers LIKE '%$userID%' GROUP BY paper";
  $results = $mysqli->query($query_string) or die("failed : ".$mysqli->error." $query_string");
  if ($results->num_rows > 0) {
    echo "<br />\n";
    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>Papers for Review</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  }
  while ($row = $results->fetch_assoc()) {
    $log_string = "SELECT DATE_FORMAT(MAX(reviewed),'%d/%m/%Y %T') AS started FROM review_comments WHERE reviewer=$userID and q_paper=" . $row['property_id'];
    $log_results = $mysqli->query($log_string);
    $reviewed = '';
    $restartdate = '';
    while ($log_row = $log_results->fetch_assoc()) {
      $reviewed = $log_row['started'];
    }
    $log_results->close();
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"#\" onclick=\"startPaper('" . $row['property_id'] . "'," . $row['fullscreen'] . "); return false;\"><img src=\"./artwork/summative.png\" width=\"48\" height=\"48\" alt=\"Paper Icon\" border=\"0\" /></a></td>\n";
    echo "  <td><a href=\"#\" onclick=\"startPaper('" . $row['property_id'] . "'," . $row['fullscreen'] . "); return false;\">" . $row['paper_title'] . "</a><br /><div style=\"color:#C00000\">Deadline: " . $row['internal_review_deadline'] . "</div>";
    if ($reviewed == '') {
      echo "<span style=\"color:white; background-color:red\">&nbsp;Not Reviewed&nbsp;</span>";
    } else {
      echo "<span style=\"color:#808080\">Reviewed: $reviewed</span>";
    }
    echo "</td></tr></table></div>\n";
  }
  if ($results->num_rows > 0) echo '<br clear="left" />';
  $results->close();
?>

<br />
<?php
  // -- Display personal folders --------------------------------------
  $module_sql = '';
  foreach ($teams as $individual_team){
    if (trim($individual_team) != '') $module_sql .= " OR team_name LIKE '%$individual_team%'";
  }

  $folder_details = $mysqli->query("SELECT id, name, team_name, color FROM folders WHERE (ownerID=$userID $module_sql) AND name NOT LIKE '%;%' AND deleted IS NULL ORDER BY name, id");

  echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>My Folders (" . ($folder_details->num_rows + 1) . ")</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  while ($row = $folder_details->fetch_assoc()) {
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"./folder/details.php?folder=" . $row['id'] . "\"><img src=\"./artwork/" . $row['color'] . "_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"./folder/details.php?folder=" . $row['id'] . "\" class=\"blacklink\">" . $row['name'] . "</a></td></tr></table></div>\n";
  }
  $folder_details->close();

  if (isset($_GET['newfolder']) AND $_GET['newfolder'] == 'y' or $duplicate_name == 1) {
    if (isset($_POST['submit']) and $_POST['submit'] and $duplicate_name == 1) {
      echo "<div class=\"f\"><img src=\"./artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" />&nbsp;<input style=\"background-color:#FFC0C0\" type=\"text\" size=\"30\" name=\"folder_name\" value=\"$new_folder_name\" onkeypress=\"if (event.keyCode == 38 || event.keyCode == 59 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94 || event.keyCode == 126) illegalChar(event.keyCode);\" /><input type=\"submit\" name=\"submit\" value=\"Create\" /></div>\n";
    } elseif (!isset($_POST['submit'])) {
      echo "<div class=\"f\"><img src=\"./artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" />&nbsp;<input type=\"text\" size=\"30\" name=\"folder_name\" value=\"New Folder\" onkeypress=\"if (event.keyCode == 38 || event.keyCode == 59 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94 || event.keyCode == 126) illegalChar(event.keyCode);\" /><input type=\"submit\" name=\"submit\" value=\"Create\" /></div>\n";
    }
  }

  $deleted_details = $mysqli->query("SELECT COUNT(property_id) AS no_deleted FROM properties WHERE deleted IS NOT NULL AND paper_ownerID=$userID");
  $deleted = $deleted_details->fetch_assoc();
  if ($deleted['no_deleted'] > 0) {
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"./delete/recycle_list.php\"><img src=\"./artwork/full_bin.png\" width=\"48\" height=\"48\" alt=\"Recycle Bin\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"./delete/recycle_list.php\" class=\"blacklink\">Recycle Bin</a></td></tr></table></div>\n";
  } else {
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"./delete/recycle_list.php\"><img src=\"./artwork/empty_bin.png\" width=\"48\" height=\"48\" alt=\"Recycle Bin\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"./delete/recycle_list.php\" class=\"blacklink\">Recycle Bin</a></td></tr></table></div>\n";
  }
  $deleted_details->close();
?>
<br clear="left" />
<?php
  if (!isset($_GET['folder']) OR $_GET['folder'] == '') {
    echo "<br />\n";
    // -- Display module folders ------------------------------------
    $module_no = count($modules_array);
    if (strpos($userroles,'Admin') !== false) $module_no++;

    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>My Modules ($module_no)</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    if (strpos($userroles,'SysAdmin') !== false) {
      echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"./folder/all.php\"><img src=\"./artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"./folder/all.php\" class=\"blacklink\"><strong>All Modules...</strong></a><br /><span style=\"color:#C00000\">(SysAdmin only)</span></td></tr></table></div>\n";
    } elseif (strpos($userroles,'Admin') !== false) {
      echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"./folder/all.php\"><img src=\"./artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"./folder/all.php\" class=\"blacklink\"><strong>All Modules in School...</strong></a><br /><span style=\"color:#C00000\">(Admin only)</span></td></tr></table></div>\n";
      //echo '<div style="font-weight:normal">&nbsp;<a style="color:blue" href="/folder/all.php">All modules</a> (Admin)</div>';
    }
    foreach ($modules_array as $folder_title => $url) {
	    $title_parts = explode(' - ',$folder_title);
	    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"$url\"><img src=\"./artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"$url\" class=\"blacklink\">" . $title_parts[0] . "</a><br /><span style=\"color:#808080\">" . $title_parts[1] . "</span></td></tr></table></div>\n";
    }

    echo '<br clear="left" /><br />';

    // -- Display papers not assigned to a module -------------------
    $paper_data = $mysqli->query("SELECT DISTINCT property_id, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'%d/%m/%y %H:%i') AS display_start_date, DATE_FORMAT(end_date,'%d/%m/%y %H:%i') AS display_end_date, exam_duration FROM properties LEFT JOIN papers ON properties.property_id=papers.paper WHERE paper_ownerID=$userID AND moduleID='' AND deleted IS NULL GROUP BY paper_title ORDER BY paper_title");
    if ($paper_data->num_rows > 0) {
      echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>Unassigned Papers (" . $paper_data->num_rows . ")<nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
      while ($row = $paper_data->fetch_assoc()) {
        displayPaperIcon($row);
      }
    }
  }

  $mysqli->close();
?>
</div>
</div>

</body>
</html>