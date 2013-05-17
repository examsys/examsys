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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../include/sort.inc';

require_once '../classes/folderutils.class.php';
require_once '../classes/paperproperties.class.php';

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);

if (!$propertyObj) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$paper_title = $propertyObj->get_paper_title();
$calendar_year = $propertyObj->get_calendar_year();
$type = $propertyObj->get_rubric();
$marking = $propertyObj->get_marking();
$review_type = $propertyObj->get_display_question_mark();

require_once 'summary_report.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['reviewsummary'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <style type="text/css">
    td {font-size:110%}
    .fn {color:#808080}
    .num {padding-top:1px; padding-bottom:1px; padding-left:15px; text-align:right; border-bottom:solid #EEEEEE 1px}
    .errnum {color:#C00000; padding-top:1px; padding-bottom:1px; padding-left:15px; text-align:right; border-bottom:solid #EEEEEE 1px}
    .title {padding-left:10px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script language="JavaScript">
  var ie  = document.all;
  var ns6 = document.getElementById&&!document.all;
  var isMenu  = false;
  var menuSelObj = null;
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
  function ItemSelMenu(tmpUserID, e) {
    if (!e) var e = window.event;
    var currentX = e.clientX;
    var currentY = e.clientY;
    var scrOfX = $('body,html').scrollLeft();
    var scrOfY = $('body,html').scrollTop();

    document.getElementById('userID').value = tmpUserID;

    document.getElementById('menudiv').style.left = currentX+scrOfX + 'px';
    document.getElementById('menudiv').style.top = currentY+scrOfY + 'px';

    document.getElementById('menudiv').style.display = "";
    document.getElementById('item1b').style.backgroundColor = '#FFFFFF';
    document.getElementById('item2b').style.backgroundColor = '#FFFFFF';

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

  function viewProfile() {
    document.getElementById('menudiv').style.display = 'none';
    window.location = '../users/details.php?paperID=<?php echo $_GET['paperID']; ?>&userID=' + document.getElementById('userID').value;
  }

  function viewReviews() {
    document.getElementById('menudiv').style.display = 'none';
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    window.open("display_form.php?paperID=<?php echo $_GET['paperID']; ?>&userID=" + document.getElementById('userID').value + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  }

  document.onmousedown = mouseSelect;
  </script>
</head>

<body>
  <div id="menudiv" style="background-color:white; padding:1px; font-size:80%; position:absolute; display:none; top:0px; left:0px; z-index:10000; border:1px solid #868686; -moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px; box-shadow:2px 2px 2px rgba(100, 100, 100, 0.50)" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
  <table width="180" cellspacing="2" cellpadding="0" border="0" style="font-size:100%; background-color:white; width:100%">
    <tr><td>
      <table width="160" cellspacing="0" cellpadding="1" border="0" style="font-size:90%; background-color:white">
        <tr>
          <td id="item1a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" border="0" /></td><td id="item1b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewReviews();"><?php echo $string['Review Form'];?></td>
        </tr>
        <tr>
          <td id="item2a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewProfile();"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="" border="0" /></td><td id="item2b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewProfile();"><?php echo $string['Student Profile'];?></td>
        </tr>
      </table>
    </td></tr>
  </table>
  </div>
<?php
  echo "<table class=\"header\" style=\"font-size:80%\">\n";
  echo "<tr><th colspan=\"" . ($heading_no + 7) . "\"><div class=\"breadcrumb\">";
  if (isset( $_GET['module'] ) and $_GET['module'] != '') {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  } elseif (isset($_GET['folder'])) {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } else {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a>';
  echo "</div><div onclick=\"qOff()\" style=\"font-size:220%; font-weight:bold; margin-left:10px\">" . $string['reviewsummary'] . "</div>";
  echo "</th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
?>
<?php

  //work out ordring
  if (isset($_GET['ordering']) and $_GET['ordering'] == 'asc') {
    $ordering = 'desc';
    $ordering_img = "<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" style=\"padding-left:5px\" />";
  } else {
    $ordering = 'asc';
    $ordering_img = "<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" style=\"padding-left:5px\" />";
  }
  if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
  } else {
    $sortby = 'surname';
  }
  if (isset($_GET['percent'])) {
    $percent =  $_GET['percent'];
  } else {
    $percent = 100;
  }

  // write out headings
  $query_string = "percent=" . $percent . "&paperID=" . $_GET['paperID'] . "&startdate=" . $_GET['startdate'] . "&enddate=" . $_GET['enddate'] . "&repmodule=" . $_GET['repmodule'] . "&repcourse=" . $_GET['repcourse'] . "&meta1=" . $_GET['meta1'] . "";
  $heading = array('surname'=>$string['name'], 'student_id'=>$string['studentid'], 'have_review'=>$string['reviewed'], 'group'=>$type);
  if ($review_type == 1) {
    $heading['review_no'] = $string['reviews'];
  }
  $i = 1;
  foreach ($questions as $questionID => $tmp_data) {
    $heading[$questionID] = $string['q'] . $i;
    $i++;
  }
  $heading['overall'] = $string['overall'];

  echo '<tr><th></th>';
  foreach ($heading as $k => $h) {
    echo '<th class="' . $k . '"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;';
    echo "<a style=\"color:black;text-decoration:none\" href=\"" . $_SERVER['PHP_SELF'] . '?' . $query_string . "&sortby=$k&ordering=$ordering" . "\">";
    echo  $h;
    if ($k == $sortby) {
      echo $ordering_img;
    }
    echo "</a>";
    echo '</th>';
  }
  echo "<th class=\"num\">&nbsp;</th></tr>\n<tr><th colspan=\"" . ($heading_no + 8) . "\" class=\"bevel\"></th></tr>";

  // Take the arrays and form one master array which can be sorted for on-screen display.
  $master_array = array();
  $user_number = 0;
  foreach ($user_data as $student_userID => $student) {
    if ($student_userID > 0) {
      $master_array[$user_number]['icon'] = ($user_data[$student_userID]['have_review']) ? 'peer_review_16.gif' : 'peer_review_retired_16.png';
      $mean_total = 0;
      $master_array[$user_number]['userid'] = $student_userID;
      $master_array[$user_number]['student_id'] = $student['student_id'];
      $master_array[$user_number]['title'] = $student['title'];
      $master_array[$user_number]['surname'] = $student['surname'];
      $master_array[$user_number]['first_names'] = $student['first_names'];

      if ($user_data[$student_userID]['have_review']) {
        $master_array[$user_number]['have_review'] = 'Complete';
      } else {
        $master_array[$user_number]['have_review'] = 'Missing';
      }
      $master_array[$user_number]['group'] = $student['group'];
      if ($review_type == 1) {
        if (isset($student['review_no'])) {
          $master_array[$user_number]['review_no'] = $student['review_no'];
          $master_array[$user_number]['group_no'] = (count($groups[$student['group']])-1);
          if ($student['review_no'] < (count($groups[$student['group']])-1)) {
            $master_array[$user_number]['reviews'] = $student['review_no'] . '/' . (count($groups[$student['group']])-1);
          } else {
            $master_array[$user_number]['reviews'] = $student['review_no'] . '/' . (count($groups[$student['group']])-1);
          }
        } else {
          $master_array[$user_number]['reviews'] = '0';
        }
        $q_no = 1;
        foreach ($questions as $questionID => $tmp_data) {
          if (isset($student['means'][$questionID])) {
            if ($_GET['percent'] == '1') {
              $master_array[$user_number]["q$q_no"] = round($student['percent'][$questionID],0) . '%';
            } else {
              $master_array[$user_number]["q$q_no"] = padDecimals($student['means'][$questionID],1);
            }
            $mean_total += $student['means'][$questionID];
          } else {
            $master_array[$user_number]["q$q_no"] = '';
          }
          $q_no++;
        }
        if ($_GET['percent'] == '1') {
          $master_array[$user_number]['overall'] = round($student['total_percent'][$questionID], 0);
        } else {
          $master_array[$user_number]['overall'] = padDecimals($mean_total / $heading_no, 2);
        }
      } else {
        $q_no = 1;
        foreach ($questions as $questionID => $tmp_data) {
          if (isset($user_data[0]['data'][$questionID][$student_userID])) {
            $master_array[$user_number]["q$q_no"] = $user_data[0]['data'][$questionID][$student_userID];
          } else {
            $master_array[$user_number]["q$q_no"] = '';
          }
          $q_no++;
        }
      }

      $user_number++;
    }
  }

  // Sort the data.
  $master_array = array_csort($master_array, $sortby, $ordering);
  
  //var_dump($master_array);

  for ($i=0; $i<$user_number; $i++) {
    //if ($student_userID > 0) {
    if ($master_array[$i]['student_id'] > 0) {
      echo '<tr>';
      echo '<td class="greyln"><img src="../artwork/' . $master_array[$i]['icon'] . '" width="16" height="16" alt="" border="0" onclick="ItemSelMenu(' . $master_array[$i]['userid'] . ', event);" /></td>';
      echo '<td class="greyln" onclick="ItemSelMenu(' . $master_array[$i]['userid'] . ', event);">' . $master_array[$i]['title'] . ' ' . $master_array[$i]['surname'] . ', <span class="fn">' . $master_array[$i]['first_names'] . '</span></td>';
      echo '<td class="greyln">' . $master_array[$i]['student_id'] . '</td>';
      if ($master_array[$i]['have_review'] == 'Complete') {
        echo '<td class="greyln">' . $string['Complete'] . '</td>';
      } else {
        echo '<td class="greyln" style="color:#C00000">' . $string['Missing'] . '</td>';
      }
      echo '<td class="greyln">' . $master_array[$i]['group'] . '</td>';
      if ($review_type == 1) {
        if (isset($master_array[$i]['review_no'])) {
          if ($master_array[$i]['review_no'] < $master_array[$i]['group_no']) {
            echo '<td class="errnum">' . $master_array[$i]['reviews'] . '</td>';
          } else {
            echo '<td class="num">' . $master_array[$i]['reviews'] . '</td>';
          }
        } else {
          echo '<td class="errnum">0</td>';
        }
        $q_no = 1;
        foreach ($questions as $questionID => $tmp_data) {
          echo '<td class="num">' . $master_array[$i]["q$q_no"] . '</td>';
          $q_no++;
        }
        if ($_GET['percent'] == '1') {
          echo "<td class=\"num\">" . $master_array[$i]['overall'] . "%</td>\n";
        } else {
          echo '<td class="num">' . $master_array[$i]['overall'] . '</td>';
        }
      } else {
        $q_no = 1;
        foreach ($questions as $questionID => $tmp_data) {
          echo '<td class="num">' . $master_array[$i]["q$q_no"] . '</td>';
          $q_no++;
        }
        echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
      }
      echo "<td class=\"num\">&nbsp;</td></tr>\n";
    }
  }
  /*
  if ($review_type != '1') {
    echo '<tr>';
    echo '<td class="greyln">&nbsp;</td>';
    echo '<td class="greyln" colspan="4"><strong>Mean</strong></td>';
    foreach ($questions as $questionID => $tmp_data) {
      if (isset($user_data[0]['means'][$questionID])) {
        echo '<td class="num"><strong>' . padDecimals($user_data[0]['means'][$questionID], 2) . '</strong></td>';
      } else {
        echo '<td class="num">&nbsp;</td>';
      }
    }
    echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
  }
  */
?>
</table>

<form>
<input type="hidden" id="userID" value="" />
<input type="hidden" id="scrOfY" value="" />
</form>

</body>
</html>