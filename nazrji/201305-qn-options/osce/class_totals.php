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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

require_once '../include/demo_replace.inc';
require_once '../include/errors.inc';
require_once '../include/sort.inc';
require_once './osce.inc';

require_once '../classes/paperutils.class.php';
require_once '../classes/paperproperties.class.php';

$demo = is_demo($userObject);
$sortby = '';
$ordering = '';

$paperID   = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate   = check_var('enddate', 'GET', true, false, true);

if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);
if (!$propertyObj) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$paper = $propertyObj->get_paper_title();
$crypt_name = $propertyObj->get_crypt_name();

$user_results = load_results($propertyObj, $demo, $configObject, $mysqli);
$user_no = count($user_results);
if ($propertyObj->get_pass_mark() == 101) {
  $borderline_method = true;
} else {
  $borderline_method = false;
}

if ($borderline_method) {
  $passmark = getBlinePassmk($user_results, $user_no, $propertyObj);
} elseif ($propertyObj->get_pass_mark() != 102) {
  $passmark = $propertyObj->get_pass_mark();
} else {
  $passmark = 'N/A';
}

set_classification($user_results, $passmark, $user_no, $string);
$user_results = array_csort($user_results, $sortby, $ordering);

$completed_no = 0;
$total_score = 0;
$classifications = array(''=>'', 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 'ERROR'=>0);

for ($i=0; $i<$user_no; $i++) {
  if ($user_results[$i]['started'] != '') {   // No attendance
    $classifications[$user_results[$i]['rating']]++;
    $total_score += $user_results[$i]['numeric_score'];
    $completed_no++;
  }
}

rating_num_text($user_results, $user_no, $propertyObj, $string);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rogō: <?php echo $string['classtotals'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
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
      var currentX = e.clientX;
      var currentY = e.clientY;
      var scrOfX = $('body,html').scrollLeft();
      var scrOfY = $('body,html').scrollTop();

      document.getElementById('started').value = tmpStarted;
      document.getElementById('userID').value = currentUserID;

      top_pos = currentY+scrOfY;
      if (top_pos > ($(window).height() + scrOfY - 75)) {
        top_pos = $(window).height() + scrOfY - 75;
      }
      document.getElementById('menudiv').style.left = e.clientX+scrOfX + 'px';
      document.getElementById('menudiv').style.top = top_pos + 'px';

      document.getElementById('menudiv').style.display = "block";
      document.getElementById('item1b').style.backgroundColor = '#FFFFFF';
      document.getElementById('item2b').style.backgroundColor = '#FFFFFF';
      document.getElementById('item3b').style.backgroundColor = '#FFFFFF';
      isMenu = true;
      return false;
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
      window.open("view_form.php?paperID=<?php echo $paperID; ?>&userID=" + document.getElementById('userID').value + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewFeedback() {
      document.getElementById('menudiv').style.display = 'none';
      var winwidth = screen.width-80;
      var winheight = screen.height-80;
      window.open("/mapping/user_feedback.php?id=<?php echo $crypt_name; ?>&userID=" + document.getElementById('userID').value + "&started=" + document.getElementById('started').value + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
    
    function viewProfile() {
      document.getElementById('menudiv').style.display = 'none';
      window.top.location = '/users/details.php?userID=' + document.getElementById('userID').value;
    }
    
    document.onmousedown = mouseSelect;
  </script>
  </head>

  <body>

<div id="menudiv" class="popupmenu" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
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
  
  //output table heading
  if ($borderline_method) {
    $table_order = array(''=>'', $string['name']=>'name', $string['studentid']=>'student_id', $string['course']=>'grade', $string['total']=>'numeric_score', $string['rating']=>'rating', $string['classification']=>'classification', $string['starttime']=>'started', $string['examiner']=>'examiner');
  } else {
    $table_order = array(''=>'', $string['name']=>'name', $string['studentid']=>'student_id', $string['course']=>'grade', $string['total']=>'numeric_score', $string['classification']=>'classification', $string['starttime']=>'started', $string['examiner']=>'examiner');
  }
  $metadata_cols = array();
  if (isset($user_results[0])){
    foreach ($user_results[0] as $key => $val) {
      if (strrpos($key,'meta_') !== false) {
        $key_display = ucfirst(str_replace('meta_','',$key));
        $table_order[$key_display] = $key;
        $metadata_cols[$key] = $key;
      }
    }
  }
  
  $column_no = count($table_order) + count($metadata_cols);

  echo "<table class=\"header\" style=\"font-size:80%\">\n";
  echo "<tr><th class=\"h\" colspan=\"" . ($column_no - 1) . "\">";
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title = sprintf($string['classtotalsmodule'], $_GET['repmodule']);
  } else {
    $report_title = $string['classtotals'];
  }

  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';
  
  echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">$report_title</span></th><th class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";

  if (isset($_GET['folder'])) {
    $tmp_folder = '&folder=' . $_GET['folder'];
  } else {
    $tmp_folder = '';
  }

  if (isset($_GET['module'])) {
    $tmp_module = '&module=' . $_GET['module'];
  } else {
    $tmp_module = '';
  }
  
  // output table header
  if (isset($user_results[0])) {
    echo "<tr style=\"font-size:110%\">\n";
    foreach ($table_order as $display => $key) {
      if ($key == '') {
        echo "<th>";
      } else {
        echo "<th class=\"vert_div\">&nbsp;";
      }
      if ($sortby == $key and $ordering == 'asc') {
        echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repmodule=" . $_GET['repmodule'] . "&repcourse=" . $_GET['repcourse'] . $tmp_module . $tmp_folder . "&startdate=$startdate&enddate=$enddate&sortby=$key&ordering=desc&percent=$percent&absent=$absent\">$display</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
      } elseif ($sortby == $key and $ordering == 'desc') {
        echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repmodule=" . $_GET['repmodule'] . "&repcourse=" . $_GET['repcourse'] . $tmp_module . $tmp_folder . "&startdate=$startdate&enddate=$enddate&sortby=$key&ordering=asc&percent=$percent&absent=$absent\">$display</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th>";
      } else {
        echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&repmodule=" . $_GET['repmodule'] . "&repcourse=" . $_GET['repcourse'] . $tmp_module . $tmp_folder . "&startdate=$startdate&enddate=$enddate&sortby=$key&ordering=asc&percent=$percent&absent=$absent\">$display</a>&nbsp;</th>";
      }
    }
    echo "</tr>\n";
  }
  
  echo "\n<tr><th colspan=\"" . $column_no . "\" class=\"bevel\"></th></tr>\n";

  for ($i=0; $i<$user_no; $i++) {
    if ($user_results[$i]['started'] == '') {   // No attendance
      echo "<tr class=\"nonattend\"><td>&nbsp;</td><td>&nbsp;<a class=\"user\" href=\"../users/details.php?userID=" . $user_results[$i]['userID'] . "\">" . $user_results[$i]['display_name'] . "</a></td><td>&nbsp;" . $user_results[$i]['student_id'] . "</td><td colspan=\"" . ($column_no - 2) . "\" style=\"text-align:center\">&lt;" . $string['noattendance'] . "&gt;</td></tr>\n";
    } else {
      echo "<tr><td class=\"greyln\"><img src=\"../artwork/osce_16.gif\" style=\"cursor:hand\" onclick=\"ItemSelMenu('" . $user_results[$i]['userID'] . "', event);\" width=\"16\" height=\"16\" /></td>";
      echo '<td class="greyln';
      if ($sortby == 'name') echo ' ordered';
      echo "\">&nbsp;<span style=\"cursor:hand\" onclick=\"popMenu('" . $user_results[$i]['started'] . "', '" . $user_results[$i]['userID'] . "', event);\">" . $user_results[$i]['title'] . " " . $user_results[$i]['surname'] . ", <span style=\"color:#808080\">" . $user_results[$i]['first_names'] . "</span></td>";
      echo '<td class="greyln';
      if ($sortby == 'student_id') echo ' ordered';
      echo "\">&nbsp;" . $user_results[$i]['student_id'] . "</td>";
      echo '<td class="greyln';
      if ($sortby == 'grade') echo ' ordered';
      echo "\">&nbsp;" . $user_results[$i]['grade'] . "</td>";
      echo '<td class="greyln';
      if ($sortby == 'numeric_score') echo ' ordered';
      echo "\">&nbsp;" . $user_results[$i]['numeric_score'] . "</td>";
            
      if ($borderline_method) {
        echo '<td class="greyln';
        if ($sortby == 'rating') echo ' ordered';
        echo "\">&nbsp;" . $user_results[$i]['rating'] . "</td>\n";
      }
      
      echo '<td class="greyln';
      if ($sortby == 'classification') echo ' ordered';
      echo "\">&nbsp;" . $user_results[$i]['classification'] . "</td>";
      echo '<td class="greyln';
      if ($sortby == 'started') echo ' ordered';
      echo "\">&nbsp;" . $user_results[$i]['display_started'] . "</td>\n";
      echo '<td class="greyln';
      if ($sortby == 'examiner') echo ' ordered';
      echo "\">&nbsp;" . $user_results[$i]['examiner'] . "</td></tr>\n";
    }
  }

  echo "<tr><td colspan=\"" . $column_no . "\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"" . $column_no . "\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>" . $string['summary'] . "</td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:80%; line-height:150%\">\n";
  echo "<tr><td align=\"right\" style=\"width:110px\">" . $string['cohortsize'] . "</td><td style=\"text-align:right; width:40px\">" . $user_no . "</td></tr>\n";
  
  if ($borderline_method) {
    echo "<tr><td align=\"right\">" . $string['passmark'] . "</td><td style=\"text-align:right\">" . round($passmark, 2) . "</td><td>% (borderline method)</td></tr>\n";
  } elseif ($propertyObj->get_pass_mark() != 102) {  // Not the N/A option
    echo "<tr><td align=\"right\">" . $string['passmark'] . "</td><td style=\"text-align:right\">" . $propertyObj->get_pass_mark() . "</td><td>%</td></tr>\n";
  }
  
  $labels = get_labels($propertyObj);
  foreach ($labels as $i => $label) {
    echo "<tr><td align=\"right\">" . $string[strtolower($label)] . "</td><td style=\"text-align:right\">" . $classifications[$i] . "</td></tr>\n";
  }
  echo "</table>\n";
  
  echo "</td></tr>\n";
  echo "</table>\n";
  
  $mysqli->close();
?>
<input type="hidden" id="userID" value="" />
<input type="hidden" id="started" value="" />
</body>
</html>
