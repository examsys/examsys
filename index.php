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
* Rogō hompage. Uses ../include/options_menu.inc for the sidebar menu.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once './include/staff_student_auth.inc';
require_once './include/errors.php';
require_once './include/sidebar_menu.inc';
require_once './config/index.inc';

$userObject = UserObject::get_instance();

// Redirect Students (if not also staff), External Examiners and Invigilators to their own areas.
if ($userObject->has_role('Student') and !($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')))) {
  header("location: ./paper/");
  exit();
} elseif ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {
  header("location: ./reviews/");
  exit();
} elseif ($userObject->has_role('Invigilator')) {
  header("location: ./invigilator/");
  exit();
}

// If we're still here we should be staff
require_once './include/staff_auth.inc';

// Check for any news/announcements
$announcements = announcement_utils::get_staff_announcements($mysqli);

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="./css/body.css" />
  <link rel="stylesheet" type="text/css" href="./css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="./css/header.css" />
  <link rel="stylesheet" type="text/css" href="./css/warnings.css" />
  <link rel="stylesheet" type="text/css" href="./css/submenu.css" />
  <?php
  if (count($announcements) > 0) {
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"./css/announcements.css\" />\n";
  }
	?>
  
	<style type="text/css">
   a {color: black}
   a:visited {color: black}
   a:link {color: black}
   .recent {margin-left:-25px; padding-bottom:9px}
   #displaycredits {position:absolute; bottom:22px; text-align:center; width:90%; cursor:pointer; color:#295AAD; font-weight:bold}
	</style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="./js/staff_help.js"></script>
  <script type="text/javascript" src="./js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="./js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="./js/toprightmenu.js"></script>
  <script type="text/javascript" src="./js/sidebar.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
      
      $('body').click(function() {
        hideMenus();
      });
      
		});
		
    function startPaper(paperID, fullsc) {
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      if (fullsc == 0) {
        window.open("./reviews/start.php?id="+paperID+"&review=1","paper","width="+winwidth+",height="+winheight+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      } else {
        window.open("./reviews/start.php?id="+paperID+"&review=1","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      }
    }
    
    function illegalChar(codeID) {
      if (codeID == 59) {
        alert("Character ';' illegal - please use alternative characters in folder name.");
      }
      event.returnValue = false;
    }

    function newPaper(paperID) {
      notice = window.open("./paper/new_paper1.php?folder=","properties","width=750,height=500,left="+(screen.width/2-375)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }
    
    function hideAnnouncement(announcementID) {
      $('#announcement' + announcementID).hide();
      
      var request = $.ajax({
        url: "./ajax/staff/hide_announcement.php",
        type: "get",
        data: {announcementID: announcementID},
        timeout: 30000, // timeout after 30 seconds
        dataType: "html",
      });
    }
  </script>
</head>

<body>

<?php
  require './include/options_menu.inc';
  require './include/toprightmenu.inc';
  require './include/icon_display.inc';
	
	echo draw_toprightmenu();
?>

<div id="content">
<form id="theform" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
<?php
  // -- Create new folder ---------------------------------------------------
  $duplicate_folder = false;
  if (isset($_POST['submit'])) {
    $new_folder_name = $_POST['folder_name'];

    $duplicate_folder = folder_utils::folder_exists($new_folder_name, $userObject, $mysqli);
    if ($duplicate_folder == false) {
      folder_utils::create_folder($new_folder_name, $userObject, $mysqli);
    }
  }
?>

<div class="head_title">
  <div><img src="./artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div style="padding:6px 6px 6px 16px">
    <img src="./artwork/r_logo.gif" alt="logo" class="logo_img" />
    <div class="logo_lrg_txt">Rog&#333;</div>
    <div class="logo_small_txt"><?php echo $string['eassessmentmanagementsystem'] ?></div>
  </div>
</div>
<?php
  $as_pos = strpos($configObject->get('cfg_install_type'),' as ');
  if ($as_pos !== false) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"width:40px\"><div class=\"greywarn\"><img src=\"./artwork/agent.png\" width=\"32\" height=\"32\" alt=\"Impersonate\" /></div></td><td><div class=\"greywarn\">" . $string['loggedinas'] . " " . substr($configObject->get('cfg_install_type'), ($as_pos+4)) . "</div></td></tr></table>\n";
  }
  
  $staff_team_array = $userObject->get_staff_team_modules();
  $module_no = count($staff_team_array);
  if ($module_no == 0) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"width:40px\"><div class=\"redwarn\"><img src=\"./artwork/exclamation_red_bg.png\" width=\"32\" height=\"32\" alt=\"Warning\" /></div></td><td><div class=\"redwarn\"><strong>" . $string['warning'] . "</strong> " . $string['nomodules'] . " <a href=\"mailto:" . $configObject->get('support_email') . "\" style=\"color:#316AC5\">" . $configObject->get('support_email') . "</div></td></tr></table>\n";
  }
  
  
?>
<div style="padding-top:6px; padding-left:6px; padding-right:14px">
<?php
  // Check for any news/announcements
  foreach ($announcements as $announcement) {
    if (!isset($_SESSION['announcement' . $announcement['id']])) {
      echo "<div class=\"announcement\" id=\"announcement" . $announcement['id'] . "\"><img src=\"./artwork/close_note.png\" style=\"display:block; float:right\" onclick=\"hideAnnouncement(" . $announcement['id'] . ")\" /><div style=\"min-height:64px; padding-left:80px; padding-top:5px; background: transparent url('./artwork/" . $announcement['icon'] . "') no-repeat 5px 5px;\"><strong>" . $announcement['title'] . "</strong><br />\n<br />\n" . $announcement['msg'] . "</div></div>\n";
    }  
  }  
  
  // -- Display any papers for review ---------------------------------
  $internalreview = new internalreview($mysqli);
  $review_papers = $internalreview->get_review_papers($userObject->get_user_ID());

  if (count($review_papers) > 0) {
    echo "<div class=\"subsect_table\" style=\"clear:both\"><div class=\"subsect_title\"><nobr>" . $string['papersforreview'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
  }
  foreach($review_papers as $review_paper) {
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"#\" onclick=\"startPaper('" . $review_paper['crypt_name'] . "'," . $review_paper['fullscreen'] . "); return false;\">" . Paper_utils::displayIcon($review_paper['type'], '', '', '', '', '') . "</a></td>\n";
    echo "  <td><a href=\"#\" onclick=\"startPaper('" . $review_paper['crypt_name'] . "'," . $review_paper['fullscreen'] . "); return false;\">" . $review_paper['paper_title'] . "</a><br /><div style=\"color:#C00000\">" . $string['deadline'] . " " . $review_paper['internal_review_deadline'] . "</div>";
    if ($review_paper['reviewed'] == '') {
      echo "<span style=\"color:white; background-color:#FF4040\">&nbsp;" . $string['notreviewed'] . "&nbsp;</span>";
    } else {
      echo "<span style=\"color:#808080\">" . $string['reviewed'] . ": {$review_paper['reviewed']}</span>";
    }
    echo "</td></tr></table></div>\n";
  }
  if (count($review_papers) > 0) echo '<br clear="left" />';

  if(!$userObject->has_role('Standards Setter')) {
    // -- Display personal folders --------------------------------------
    $module_sql = '';
    if (count($userObject->get_staff_modules()) > 0) {
      $module_sql = " OR idMod IN (" . implode(',', array_keys($userObject->get_staff_modules())) . ")";
    }

    $result = $mysqli->prepare("SELECT DISTINCT id, name, color FROM folders LEFT JOIN folders_modules_staff ON folders.id = folders_modules_staff.folders_id WHERE (ownerID = ? $module_sql) AND name NOT LIKE '%;%' AND deleted IS NULL ORDER BY name, id");
    $result->bind_param('i', $userObject->get_user_ID());
    $result->execute();
    $result->bind_result($id, $name, $color);
    $result->store_result();

    echo "<div class=\"subsect_table\" style=\"clear:both\"><div class=\"subsect_title\"><nobr>" . $string['myfolders'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
    while ($result->fetch()) {
      echo "<div class=\"f\" ><div class=\"f_icon\"><a href=\"./folder/index.php?folder=$id\"><img src=\"./artwork/" . $color . "_folder.png\"  alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"./folder/index.php?folder=$id\">$name</a></div></div>\n";
    }
    $result->close();

    if (isset($_GET['newfolder']) and $_GET['newfolder'] == 'y' or $duplicate_folder == true) {
      if (isset($_POST['submit']) and $_POST['submit'] and $duplicate_folder == true) {
        echo "<script>alert(\"" . $string['duplicatefoldername'] . "\")</script>";
        echo "<div class=\"f\"><div class=\"f_icon\"><img src=\"./artwork/yellow_folder.png\" alt=\"Folder\" /></div><div class=\"f_details\"><input class=\"errfield\" type=\"text\" size=\"30\" name=\"folder_name\" value=\"$new_folder_name\" required onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><br /><input type=\"submit\" name=\"submit\" class=\"ok\" style=\"width:90px; margin:1px; padding:3px\" value=\"" . $string['create'] . "\" /></div></div>\n";
      } elseif (!isset($_POST['submit'])) {
        echo "<div class=\"f\"><div class=\"f_icon\"><img src=\"./artwork/yellow_folder.png\" alt=\"Folder\" /></div><div class=\"f_details\"><input type=\"text\" size=\"30\" name=\"folder_name\" value=\"\" placeholder=\"" . $string['foldername'] . "\" required onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><br /><input type=\"submit\" name=\"submit\" class=\"ok\" style=\"width:90px; margin:1px; padding:3px\" value=\"" . $string['create'] . "\" /></div></div>\n";
      }
    }

    echo "<div class=\"f\"><div class=\"f_icon\"><a href=\"./delete/recycle_list.php\"><img src=\"./artwork/recycle_bin.png\" alt=\"" . $string['recyclebin'] . "\" /></a></div><div class=\"f_details\"><a href=\"./delete/recycle_list.php\">" . $string['recyclebin'] . "</a></div></div>\n";
?>
<br clear="left" />
<?php
  }

  echo "<br />\n";
  // -- Display modules ------------------------------------
  echo "<div class=\"subsect_table\" style=\"clear:both\"><div class=\"subsect_title\"><nobr>" . $string['mymodules'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";

  if ($userObject->has_role('SysAdmin')) {
    echo "<div style=\"margin-left:38px; margin-bottom:20px\"><a href=\"./module/all.php\" style=\"color:#295AAD\">" . $string['allmodules']  . "</a></div>\n";
  } elseif ($userObject->has_role('Admin')) {
    echo "<div style=\"margin-left:38px; margin-bottom:20px\"><a href=\"./module/all.php\" style=\"color:#295AAD\">" . $string['allmodulesinschool']  . "</a></div>\n";
  }
  foreach ($staff_team_array as $idMod => $folder_title) {
    $url = './module/index.php?module=' . $idMod;
    echo "<div class=\"f\"><div class=\"f_icon\"><a href=\"$url\"><img src=\"./artwork/yellow_folder.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"$url\">" . $folder_title['code'] . "</a><br /><span class=\"grey\">" . str_replace('&', '&amp;', $folder_title['fullName']) . "</span></div></div>\n";
  }
  // Display un-assigned papers / questions.
  $paper_utils = new PaperUtils();
  if ($paper_utils->count_unassigned_papers($userObject->get_user_ID(), $mysqli) or $paper_utils->count_unassigned_questions($userObject->get_user_ID(), $mysqli) ) {
    $url = './module/index.php?module=0';
    echo "<div class=\"f\"><div class=\"f_icon\"><a href=\"$url\"><img src=\"./artwork/red_folder.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"$url\">" . $string['unassigned'] . "</a><br /><span class=\"grey\">" . $string['unassignedmsg'] . "</span></div></div>\n";
  }

  $mysqli->close();
?>
</div>
</form>
</div>
</body>
</html>
