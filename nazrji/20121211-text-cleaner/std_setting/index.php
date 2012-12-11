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
require '../include/std_set_shared_functions.inc';

$paperID = $_GET['paperID'];

function displayReview($review, $userObj) {
  global $REPLACEMEuserIDold, $userroles;
  
  $setter_id = $review['setter_id'];
  
  if ($review['review_total'] == $review['total_marks']) {
    $icon = '../artwork/std_set_icon_16.gif';
    $text_color  = 'black';
    $background = 'white';
  } else {
    $icon = '../artwork/std_set_icon_problem.gif';
    $text_color  = '#800000';
    $background = '#FFC0C0';
  }
  if ($review['group_review'] != 'No') {
    $icon = '../artwork/small_users_icon.png';
    $setter_id = $review['setter_id'] . ',' . $review['date'] . ';' . $review['group_review'];
  }
  
  $html = '';
  if ($setter_id == $userObj->get_user_ID() or $userObj->has_role('SysAdmin')) {
    $html .= "<tr id=\"review{$review['review_no']}\" style=\"cursor:hand\" onmouseover=\"highlight({$review['review_no']})\" onmouseout=\"unhighlight({$review['review_no']})\" onclick=\"selReview('$setter_id','{$review['date']}',{$review['review_no']},'{$review['method']}','menu2b','{$review['group_review']}',event); return false;\" ondblclick=\"editReview(); return false;\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" border=\"0\" /></td><td>&nbsp;";
  } else {
    $html .= "<tr id=\"review{$review['review_no']}\" style=\"cursor:hand\" onmouseover=\"highlight({$review['review_no']})\" onmouseout=\"unhighlight({$review['review_no']})\" onclick=\"selReview('$setter_id','{$review['date']}',{$review['review_no']},'{$review['method']}','menu2c','{$review['group_review']}',event); return false;\" ondblclick=\"editReview(); return false;\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" border=\"0\" /></td><td>&nbsp;";
  }
  if ($review['distinction_score'] != 'n/a') $review['distinction_score'] .= '%';
  if ($review['group_review'] != 'No') {
    $html .= "&lt;group review&gt;</a>";
  } else {
    $html .= "{$review['name']}</a>";
  }
  if ($review['review_total'] == $review['total_marks']) {
    $html .= "</td><td>&nbsp;{$review['display_date']}</td><td style=\"text-align:right\">{$review['pass_score']}%&nbsp;</td><td style=\"text-align:right\">{$review['distinction_score']}&nbsp;</td><td style=\"text-align:right\">{$review['review_total']}&nbsp;</td><td style=\"text-align:right\">{$review['total_marks']}&nbsp;</td><td>&nbsp;{$review['method']}</td><td></td></tr>\n";
  } else {
    $html .= "</td><td>&nbsp;{$review['display_date']}</td><td style=\"text-align:right\">{$review['pass_score']}%&nbsp;</td><td style=\"text-align:right\">{$review['distinction_score']}&nbsp;</td><td style=\"text-align:right; color:$text_color; background-color:$background\">{$review['review_total']}&nbsp;</td><td style=\"text-align:right; color:$text_color; background-color:$background\">{$review['total_marks']}&nbsp;</td><td>&nbsp;{$review['method']}</td><td></td></tr>\n";
  }
  return $html;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['listsettings'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script language="JavaScript">
    var groupReview;

    function selReview(setterID, dateID, reviewID, methodType, menuID, group, evt) {
      groupReview = group;

      tmp_ID = document.StdSetMenu.oldReviewID.value;
      if (tmp_ID != '') {
        document.getElementById('review' + tmp_ID).style.backgroundColor = 'white';
        document.getElementById('review' + tmp_ID).style.color = 'black';
      }
      document.getElementById('menu2a').style.display = 'none';
      document.getElementById('menu2b').style.display = 'none';
      document.getElementById('menu2c').style.display = 'none';
      document.getElementById(menuID).style.display = 'block';

      document.StdSetMenu.setterID.value = setterID;
      document.StdSetMenu.dateID.value = dateID;
      document.StdSetMenu.method.value = methodType;

      document.getElementById('review' + reviewID).style.backgroundColor = '#316AC5';
      document.getElementById('review' + reviewID).style.color = 'white';
      document.StdSetMenu.oldReviewID.value = reviewID;
      evt.cancelBubble = true;
    }

    function reviewOff() {
      document.getElementById('menu2a').style.display = 'block';
      document.getElementById('menu2b').style.display = 'none';
      document.getElementById('menu2c').style.display = 'none';
      tmp_ID = document.StdSetMenu.oldReviewID.value;
      if (tmp_ID != '') {
        document.getElementById('review' + tmp_ID).style.backgroundColor = 'white';
        document.getElementById('review' + tmp_ID).style.color = 'black';
      }
    }

    function highlight(lineID) {
      if (lineID != document.StdSetMenu.oldReviewID.value) {
        document.getElementById('review' + lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function unhighlight(lineID) {
      if (lineID != document.StdSetMenu.oldReviewID.value) {
        document.getElementById('review' + lineID).style.backgroundColor = '';
      }
    }

    function roundNumber(num, dec) {
      var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
      return result;
    }
  </script>
</head>

<body onclick="reviewOff()">

<?php
$reviews_html = '';
$total_marks = 0;

$results = $mysqli->prepare("SELECT paper_title, total_mark FROM properties WHERE property_id=? LIMIT 1");
$results->bind_param('i', $paperID);
$results->execute();
$results->store_result();
$results->bind_result($paper_title, $total_mark);
while ($results->fetch()) {
  $reviews_html .= '<table class="header"><tr><th><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if (isset($_GET['module']) and $_GET['module'] != '') {
    $reviews_html .= '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  $reviews_html .= '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '&folder=' . $_GET['folder'] . '&module=' . $_GET['module'] . '">' . $paper_title . ' </a></div><div style="font-size:220%; color:black; font-weight:bold; margin-left:10px">' . $string['standardssetting'] . '</div></th><th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(97); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th></tr></table>';

  $reviews_html .= <<< TABLEHEADER
<table class="header">
  <tr>
  	<th style="width:18px">&nbsp;</td>
  	<th class="vert_div" style="width:18%">&nbsp;{$string['standardsetter']}&nbsp;</th>
  	<th class="vert_div" style="width:13%">&nbsp;{$string['date']}&nbsp;</th>
  	<th class="vert_div" style="width:10%">&nbsp;{$string['passscore']}</th>
  	<th class="vert_div" style="width:10%">&nbsp;{$string['distinction']}</th>
  	<th class="vert_div" style="width:12%">&nbsp;{$string['reviewmarks']}</th>
  	<th class="vert_div" style="width:10%">&nbsp;{$string['papertotal']}</th>
  	<th class="vert_div" style="width:14%">&nbsp;{$string['method']}</th>
  	<th class="vert_div" width="25%">&nbsp;</th>
 </tr>
 <tr><th colspan="9" class="bevel"></th></tr>
TABLEHEADER;
  $total_marks = $total_mark;
}
$results->close();

$no_reviews = 0;
$reviews = get_reviews($mysqli, 'index', $paperID, $total_marks, $no_reviews);

foreach ($reviews as $review) {
  $reviews_html .= displayReview($review, $userObject);
}
require '../include/std_set_menu.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
echo $reviews_html;
echo "</table>\n";
$mysqli->close();
?>
</body>
</html>
