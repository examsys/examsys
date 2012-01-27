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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

require_once '../include/errors.inc';

check_var('paperID', 'GET', true, false);

require 'summary_report.inc';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Report</title>
  
  <style>
    body {background-color:white; color:black; font-family:Arial,sans-serif; font-size:90%; margin:0px}
    .fn {color:#808080}
    .num {padding-top:1px; padding-bottom:1px; text-align:right; border-bottom:solid #EEEEEE 1px}
    .title {padding-left:10px}
    .line {padding-top:1px; padding-bottom:1px; padding-left:6px; border-bottom:solid #EEEEEE 1px}
    .breadcrumb {margin-top:2px; margin-left:10px; font-size:90%}
    .breadcrumb a:link {color:blue; text-decoration:none; cursor:pointer}
    .breadcrumb a:visited {color:blue; text-decoration:none; cursor:pointer}
    .breadcrumb a:hover {color:blue; text-decoration:underline; cursor:pointer}
  </style>
  
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

    var scrOfX = getScrollX();
    var scrOfY = getScrollY();

    document.getElementById('userID').value = tmpUserID;
    
    document.getElementById('menudiv').style.left = currentX+scrOfX + 'px';
    document.getElementById('menudiv').style.top = currentY+scrOfY + 'px';

    document.getElementById('menudiv').style.display = "";
    document.getElementById('item1b').style.backgroundColor='#FFFFFF';
    document.getElementById('item2b').style.backgroundColor='#FFFFFF';

    isMenu = true;
    return false ;
  }

  function getScrollX() {
    var scrollOfX = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrollOfX = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrollOfX = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrollOfX = document.documentElement.scrollLeft;
    }
	return scrollOfX;
  }
  
  function getScrollY() {
    var scrollOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrollOfY = window.pageYOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrollOfY = document.body.scrollTop;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrollOfY = document.documentElement.scrollTop;
    }
	return scrollOfY;
  }
  function menuRowOn(rowID) {
    // Left menu column
    document.getElementById('item'+rowID+'a').style.backgroundColor='#FFE7A2';
    document.getElementById('item'+rowID+'a').style.borderTop='1px solid #FFBD69';
    document.getElementById('item'+rowID+'a').style.borderBottom='1px solid #FFBD69';
    document.getElementById('item'+rowID+'a').style.borderLeft='1px solid #FFBD69';

    // Right menu column
    document.getElementById('item'+rowID+'b').style.backgroundColor='#FFE7A2';
    document.getElementById('item'+rowID+'b').style.borderTop='1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderBottom='1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderRight='1px solid #FFBD69';
    document.getElementById('item'+rowID+'b').style.borderLeft='1px solid #FFE7A2';
  }

  function menuRowOff(rowID) {
    // Left menu column
    document.getElementById('item'+rowID+'a').style.backgroundColor='#F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderTop='1px solid #F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderBottom='1px solid #F1F5FB';
    document.getElementById('item'+rowID+'a').style.borderLeft='1px solid #F1F5FB';

    // Right menu column
    document.getElementById('item'+rowID+'b').style.backgroundColor='#FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderTop='1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderBottom='1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderRight='1px solid #FFFFFF';
    document.getElementById('item'+rowID+'b').style.borderLeft='1px solid #FFFFFF';
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
  <div id="menudiv" style="filter: progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=3); position:absolute; display:none; top:0px; left:0px;z-index:10000;" onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
  <table width="160" cellspacing="2" cellpadding="0" border="0" style="border:1px solid #6593CF; font-size:90%; background-color:white">
    <tr><td>
      <table width="160" cellspacing="0" cellpadding="1" border="0" style="font-size:100%; background-color:white">
        <tr>
          <td id="item1a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewScript();"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" border="0" /></td><td id="item1b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('1');" onmouseout="menuRowOff('1');" onclick="viewReviews();">Review Form</td>
        </tr>
        <tr>
          <td id="item2a" style="text-align:center; border-top:1px solid #F1F5FB; border-bottom:1px solid #F1F5FB; border-left:1px solid #F1F5FB; border-right:0px solid #F1F5FB; background-color:#F1F5FB; width:24px" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewProfile();"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="" border="0" /></td><td id="item2b" style="padding-left:8px; border:1px solid #FFFFFF; background-color:#FFFFFF; cursor:default" onmouseover="menuRowOn('2');" onmouseout="menuRowOff('2');" onclick="viewProfile();">Student Profile</td>
        </tr>
      </table>
    </td></tr>
  </table>
  </div>
<?php
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td style=\"background-color:#F1F5FB\" colspan=\"" . ($heading_no + 7) . "\"><div class=\"breadcrumb\">";
  if ($moduleID != '') {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $moduleID_text . '">' . $moduleID_text . '</a>';
  } elseif ($folder != '') {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } else {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a>';
  echo "</div><div onclick=\"qOff()\" style=\"font-size:220%; font-weight:bold; margin-left:10px\">$paper_title</div>";
  echo "</td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></td></tr>\n";
?>
<?php
  // write out headings
  echo '<tr style="background-color:#F1F5FB"><td></td><td><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Name</td><td><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Student ID</td><td><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Reviewed</td><td><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $type . '</td><td><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Reviews</td>';
  for ($i=1; $i<=$heading_no; $i++) {
    echo '<td><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Q' . $i . '</td>';
  }
  echo '<td><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Overall</td><td style="width:20%">&nbsp;</td></tr>';
  echo '<tr><td colspan="' . ($heading_no + 8) . '" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>';

  foreach ($user_data as $student_userID => $student) {
    $mean_total = 0;
    echo '<tr>';
    echo '<td class="line"><img src="../artwork/peer_review_16.gif" width="16" height="16" alt="" border="0" onclick="ItemSelMenu(' . $student_userID . ', event);" /></td>';
    echo '<td class="line" onclick="ItemSelMenu(' . $student_userID . ', event);">' . $student['title'] . ' ' . $student['surname'] . ', <span class="fn">' . $student['first_names'] . '</span></td>';
    echo '<td class="line">' . $student['student_id'] . '</td>';
    if (isset($reviewers[$student['userID']])) {
      echo '<td class="line">Complete</td>';
    } else {
      echo '<td class="line" style="color:#C00000">Missing</td>';
    }
    echo '<td class="line">' . $student['group'] . '</td>';
    if (isset($student['review_no'])) {
      echo '<td class="num">' . $student['review_no'] . '</td>';
    } else {
      echo '<td class="num">0</td>';
    }
    foreach ($questions as $questionID => $tmp_data) {
      if (isset($student['means'][$questionID])) {
        echo '<td class="num">' . padDecimals($student['means'][$questionID],2) . '</td>';
        $mean_total += $student['means'][$questionID];
      } else {
        echo '<td class="num">&nbsp;</td>';
      }
    }
    echo '<td class="num">' . padDecimals($mean_total / $heading_no, 2) . '</td>';
    echo "<td class=\"num\">&nbsp;</td></tr>\n";
  }
?>
</table>

<form>
<input type="hidden" id="userID" value="" />
<input type="hidden" id="scrOfY" value="" />
</form>

</body>
</html>