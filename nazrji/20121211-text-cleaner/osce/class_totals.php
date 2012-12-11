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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require 'class_totals.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rogō: <?php echo $string['classtotals'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script language="JavaScript">
    var ie  = document.all
    var ns6 = document.getElementById&&!document.all
    var isMenu  = false ;
    var menuSelObj = null ;
    var overpopupmenu = false;
    function mouseSelect(e) {
      var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement;
      if (isMenu) {
        if (overpopupmenu == false) {
          isMenu = false ;
          overpopupmenu = false;
          document.getElementById('menudiv').style.display = 'none';
          return true ;
        }
        return true ;
      }
      return false;
    }
    // POP UP MENU
    function popMenu(tmpStarted, currentUserID, e) {
      if (!e) var e = window.event;

      document.getElementById('started').value = tmpStarted;
      document.getElementById('tmp_userID').value = currentUserID;

      var scrOfX = 0, scrOfY = 0;
      if( typeof( window.pageYOffset ) == 'number' ) {
        //Netscape compliant
        scrOfY = window.pageYOffset;
        scrOfX = window.pageXOffset;
      } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        //DOM compliant
        scrOfY = document.body.scrollTop;
        scrOfX = document.body.scrollLeft;
      } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        //IE6 standards compliant mode
        scrOfY = document.documentElement.scrollTop;
        scrOfX = document.documentElement.scrollLeft;
      }

      document.getElementById('menudiv').style.left = e.clientX+scrOfX + 'px';
      document.getElementById('menudiv').style.top = e.clientY+scrOfY + 'px';

      document.getElementById('menudiv').style.display = "";
      document.getElementById('item1b').style.backgroundColor = '#FFFFFF';
      document.getElementById('item2b').style.backgroundColor = '#FFFFFF';
      document.getElementById('item3b').style.backgroundColor = '#FFFFFF';
      isMenu = true;
      return false ;
    }
    
    function menuRowOn(rowID) {
      // Left menu column
      document.getElementById('item'+rowID+'a').style.backgroundColor = '#FFE7A2';
      document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #FFBD69';
      
      // Right menu column
      document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFE7A2';
      document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFBD69';
      document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFE7A2';
    }
    
    function menuRowOff(rowID) {
      // Left menu column
      document.getElementById('item'+rowID+'a').style.backgroundColor = '#F1F5FB';
      document.getElementById('item'+rowID+'a').style.borderTop = '1px solid #F1F5FB';
      document.getElementById('item'+rowID+'a').style.borderBottom = '1px solid #F1F5FB';
      document.getElementById('item'+rowID+'a').style.borderLeft = '1px solid #F1F5FB';
      
      // Right menu column
      document.getElementById('item'+rowID+'b').style.backgroundColor = '#FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderTop = '1px solid #FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderBottom = '1px solid #FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderRight = '1px solid #FFFFFF';
      document.getElementById('item'+rowID+'b').style.borderLeft = '1px solid #FFFFFF';
    }    

    function viewScript() {
      document.getElementById('menudiv').style.display = 'none';
      var winwidth = 750;
      var winheight = screen.height-80;
      window.open("view_form.php?paperID=<?php echo $paperID; ?>&userID=" + document.getElementById('tmp_userID').value + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewFeedback() {
      document.getElementById('menudiv').style.display = 'none';
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("/mapping/user_feedback.php?id=<?php echo $crypt_name; ?>&userID=" + document.getElementById('tmp_userID').value + "&started=" + document.getElementById('started').value + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewProfile() {
      document.getElementById('menudiv').style.display = 'none';
      window.top.location = '/users/details.php?userID=' + document.getElementById('tmp_userID').value;
    }
  </script>
  </head>

  <body>

<div id="menudiv" style="background-color:white; padding:1px; font-size:80%; position:absolute; display:none; top:0px; left:0px; z-index:10000; border:1px solid #868686; -moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px; box-shadow:2px 2px 2px rgba(100, 100, 100, 0.50)" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
<table width="180" cellspacing="2" cellpadding="0" border="0" style="font-size:90%; background-color:white">
  <tr><td>
    <table width="180" cellspacing="0" cellpadding="1" border="0" style="font-size:100%; background-color:white">
      <tr>
        <td id="item1a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/osce_16.gif" width="16" height="16" alt="" border="0" /></td><td id="item1b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><?php echo $string['oscemarksheet']; ?></td>
      </tr>
      <tr>
        <td id="item2a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" border="0" /></td><td id="item2b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewFeedback();"><?php echo $string['feedback']; ?></td>
      </tr>
      <tr>
        <td style="background-color:#F1F5FB; width:22px"> </td><td style="padding-left:8px; text-align:right"><img src="../artwork/popup_divider.png" width="100%" height="3" border="0" alt="-" /></td>
      </tr>
      <tr>
        <td id="item3a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="" border="0" /></td><td id="item3b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('3');" onmouseout="menuRowOff('3');" onclick="viewProfile();"><?php echo $string['studentprofile']; ?></td>
      </tr>
    </table>
  </td></tr>
</table>
</div>

<?php
  $startdate = '';
  $enddate = '';
  $percent = 100;
  $absent = 0;
  $direction = 'asc';
  if (isset($_GET['startdate'])) $startdate = $_GET['startdate'];
  if (isset($_GET['enddate'])) $enddate = $_GET['enddate'];
  if (isset($_GET['percent'])) $percent = $_GET['percent'];
  if (isset($_GET['absent'])) $absent = $_GET['absent'];
  if (isset($_GET['direction'])) $direction = $_GET['direction'];
  
  
  echo "<table class=\"header\" style=\"font-size:80%\">\n";
  echo "<tr><th class=\"h\" colspan=\"7\">";
  if(isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title = sprintf($string['classtotalsmodule'], $_GET['repmodule']);
  } else {
    $report_title = $string['classtotals'];
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
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';
  
  echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">$report_title</span></th><th class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";

  echo '<tr><th class="h" style="width:16px">&nbsp;</th><th class="h"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;';
  // Name
  if ($sortby == 'name' and $ordering == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=name&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">" . $string['name'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == 'name' and $ordering == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=name&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['name'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=name&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['name'] . "</a>&nbsp;</th>";
  }
  
  // Student ID
  if ($sortby == 'student_id' and $ordering == 'asc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=student_id&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">" . $string['studentid'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == 'student_id' and $ordering == 'desc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=student_id&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['studentid'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=student_id&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['studentid'] . "</a>&nbsp;</th>";
  }
  
  // Course
  if ($sortby == 'grade' and $ordering == 'asc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=grade&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">" . $string['course'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == 'student_grade' and $ordering == 'desc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=grade&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['course'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=grade&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['course'] . "</a>&nbsp;</th>";
  }
  
  // Total
  if ($sortby == 'numeric_score' and $ordering == 'asc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=numeric_score&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">" . $string['total'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == 'numeric_score' and $ordering == 'desc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=numeric_score&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['total'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=numeric_score&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['total'] . "</a>&nbsp;</th>";
  }
  
  // Classification
  if ($sortby == 'classification' and $ordering == 'asc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=classification&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">" . $string['classification'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == 'classification' and $ordering == 'desc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=classification&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['classification'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=classification&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['classification'] . "</a>&nbsp;</th>";
  }
  
  // Start time/date
  if ($sortby == 'started' and $ordering == 'asc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=started&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">" . $string['starttime'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == 'started' and $ordering == 'desc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=started&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['starttime'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=started&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['starttime'] . "</a>&nbsp;</th>";
  }
  
  // Examiner
  if ($sortby == 'examiner' and $ordering == 'asc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=examiner&ordering=desc&percent=$percent&direction=$direction&absent=$absent\">" . $string['examiner'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } elseif ($sortby == 'examiner' and $ordering == 'desc') {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=examiner&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['examiner'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
  } else {
    echo "<th class=\"h\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repcourse=" . $_GET['repcourse'] . "&module=" . $_GET['module'] .  "&startdate=$startdate&enddate=$enddate&sortby=examiner&ordering=asc&percent=$percent&direction=$direction&absent=$absent\">" . $string['examiner'] . "</a>&nbsp;</th>";
  }
  
  echo '</tr>';
  echo "\n<tr style=\"height:4px\"><th colspan=\"8\" class=\"bevel\"></th></tr>\n";

  for ($i=0; $i<$user_no; $i++) {
    if ($user_results[$i]['started'] == '') {   // No attendance
      echo "<tr style=\"background-color:#FFC0C0\"><td>&nbsp;</td><td>&nbsp;<a class=\"user\" href=\"../users/details.php?userID=" . $user_results[$i]['tmp_userID'] . "\">" . $user_results[$i]['display_name'] . "</a></td><td>&nbsp;" . $user_results[$i]['student_id'] . "</td><td colspan=\"5\" style=\"text-align:center; font-weight:bold\">" . $string['noattendance'] . "</td></tr>\n";
    } else {
      echo "<tr><td><img src=\"../artwork/osce_16.gif\" style=\"cursor:hand\" onclick=\"ItemSelMenu('" . $user_results[$i]['tmp_userID'] . "', event);\" width=\"16\" height=\"16\" border=\"0\" alt=\"\" /></td>";
      echo '<td';
      if ($sortby == 'name') echo ' style="background-color:#F7F7F7"';
      echo ">&nbsp;<span style=\"cursor:hand\" onclick=\"popMenu('" . $user_results[$i]['started'] . "', '" . $user_results[$i]['tmp_userID'] . "', event);\">" . $user_results[$i]['title'] . " " . $user_results[$i]['surname'] . ", <span style=\"color:#808080\">" . $user_results[$i]['first_names'] . "</span></td>";
      echo "<td";
      if ($sortby == 'student_id') echo ' style="background-color:#F7F7F7"';
      echo ">&nbsp;" . $user_results[$i]['student_id'] . "</td>";
      echo "<td";
      if ($sortby == 'grade') echo ' style="background-color:#F7F7F7"';
      echo ">&nbsp;" . $user_results[$i]['grade'] . "</td>";
      echo "<td";
      if ($sortby == 'numeric_score') echo ' style="background-color:#F7F7F7"';
      echo ">&nbsp;" . $user_results[$i]['numeric_score'] . "</td>";
      echo "<td";
      if ($sortby == 'classification') echo ' style="background-color:#F7F7F7"';
      echo ">&nbsp;";
      if(isset($labels[$user_results[$i]['classification']])) echo $labels[$user_results[$i]['classification']];
      echo "</td>";
      echo "<td";
      if ($sortby == 'started') echo ' style="background-color:#F7F7F7"';
      echo ">&nbsp;" . $user_results[$i]['display_started'] . "</td>\n";
      echo "<td";
      if ($sortby == 'examiner') echo ' style="background-color:#F7F7F7"';
      echo ">&nbsp;" . $user_results[$i]['examiner'] . "</td></tr>\n";
    }
  }

  echo "<tr><td colspan=\"8\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"8\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>" . $string['summary'] . "</td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  
  echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"font-size:80%\">\n";
  echo "<tr><td align=\"right\">" . $string['cohortsize'] . "</td><td style=\"text-align:right\">" . $user_no . "</td></tr>\n";
  foreach ($labels as $i => $label) {
    echo "<tr><td align=\"right\">" . $string[strtolower($label)] . "</td><td style=\"text-align:right\">" . $classifications[$i] . "</td></tr>\n";
  }
  echo "</table>\n";
  
  echo "</td></tr>\n";
  echo "</table>\n";
  
  $mysqli->close();
?>
<input type="hidden" id="tmp_userID" value="" />
<input type="hidden" id="started" value="" />
</body>
</html>
