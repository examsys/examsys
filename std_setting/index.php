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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/std_set_shared_functions.inc';

$paperID = check_var('paperID', 'GET', true, false, true);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

function displayReview($review, $userObj) {
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
    $setter_id = $review['group_review'];
  }
  
  $html = '';
  if ($setter_id == $userObj->get_user_ID() or $userObj->has_role('SysAdmin')) {
    $html .= "<tr id=\"review{$review['std_setID']}\" class=\"l\" onclick=\"selReview(" . $review['std_setID'] . ", '$setter_id',{$review['std_setID']},'{$review['method']}','menu2b','{$review['group_review']}',event); return false;\" ondblclick=\"editReview(); return false;\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" /></td><td>&nbsp;";
  } else {
    $html .= "<tr id=\"review{$review['std_setID']}\" class=\"l\" onclick=\"selReview(" . $review['std_setID'] . ", '$setter_id',{$review['std_setID']},'{$review['method']}','menu2c','{$review['group_review']}',event); return false;\" ondblclick=\"editReview(); return false;\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" /></td><td>&nbsp;";
  }
  if ($review['distinction_score'] != 'n/a') $review['distinction_score'] .= '%';
  if ($review['group_review'] != 'No') {
    $html .= "&lt;group review&gt;</a>";
  } else {
    $html .= "{$review['name']}</a>";
  }
	if ($review['distinction_score'] == '0.000000%') {
		$review['distinction_score'] = 'top 20%';
	}
  if ($review['review_total'] == $review['total_marks']) {
    $html .= "</td><td>{$review['display_date']}</td><td class=\"no\">{$review['pass_score']}%&nbsp;</td><td class=\"no\">{$review['distinction_score']}&nbsp;</td><td class=\"no\">{$review['review_total']}&nbsp;</td><td class=\"no\">{$review['total_marks']}&nbsp;</td><td>&nbsp;{$review['method']}</td></tr>\n";
  } else {
    $html .= "</td><td>{$review['display_date']}</td><td class=\"no\">{$review['pass_score']}%&nbsp;</td><td class=\"no\">{$review['distinction_score']}&nbsp;</td><td class=\"no\" style=\"color:$text_color; background-color:$background\">{$review['review_total']}&nbsp;</td><td class=\"no\" style=\"color:$text_color; background-color:$background\">{$review['total_marks']}&nbsp;</td><td>&nbsp;{$review['method']}</td></tr>\n";
  }
  return $html;
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['listsettings'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    var groupReview;

    function selReview(std_setID, setterID, reviewID, methodType, menuID, group, evt) {
      groupReview = group;

      tmp_ID = $('#oldReviewID').val();
      if (tmp_ID != '') {
        $('#review' + tmp_ID).css('background-color', 'white');
      }
      $('#menu2a').hide();
      $('#menu2b').hide();
      $('#menu2c').hide();
      $('#' + menuID).show();

      $('#std_setID').val(std_setID);
      $('#setterID').val(setterID);
      $('#method').val(methodType);

      $('#review' + reviewID).css('background-color', '#FFBD69');
      $('#oldReviewID').val(reviewID);
      evt.cancelBubble = true;
    }

    function reviewOff() {
      $('#menu2a').show();
      $('#menu2b').hide();
      $('#menu2c').hide();
      tmp_ID = $('#oldReviewID').val();
      if (tmp_ID != '') {
        $('#review' + tmp_ID).css('background-color', 'white');
      }
    }

    function roundNumber(num, dec) {
      var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
      return result;
    }
    
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          dateFormat: '<?php echo $configObject->get('cfg_tablesorter_date_time'); ?>',
          sortList: [[1,0]] 
        });
      }
      
      $(document).click(function() {
        reviewOff();
      });

    });
  </script>
</head>

<body>

<?php
	
$reviews_html = '';
$total_marks = 0;

$paper_title  = $propertyObj->get_paper_title();
$total_mark   = $propertyObj->get_total_mark();

$reviews_html .= '<div class="head_title"><div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>';
$reviews_html .= '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
if (isset($_GET['module']) and $_GET['module'] != '') {
  $reviews_html .= '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
}
if($userObject->has_role('Standards Setter')) {
  $reviews_html .= '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" />' . $paper_title . ' </div>';
} else {
  $reviews_html .= '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '&folder=' . $_GET['folder'] . '&module=' . $_GET['module'] . '">' . $paper_title . ' </a></div>';
}
$reviews_html .= '<div class="page_title">' . $string['standardssetting'] . '</div>';
$reviews_html .= '</div>';

$reviews_html .= <<< TABLEHEADER
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0" style="width:100%">
<thead>
<tr>
  <th style="width:18px">&nbsp;</td>
  <th class="col">{$string['standardsetter']}</th>
  <th class="{sorter: 'datetime'} col">{$string['date']}</th>
  <th class="col">{$string['passscore']}</th>
  <th class="col">{$string['distinction']}</th>
  <th class="col">{$string['reviewmarks']}</th>
  <th class="col">{$string['papertotal']}</th>
  <th class="col">{$string['method']}</th>
</tr>
</thead>
<tbody>
TABLEHEADER;

$no_reviews = 0;
$reviews = get_reviews($mysqli, 'index', $paperID, $total_mark, $no_reviews);

foreach ($reviews as $review) {
  $reviews_html .= displayReview($review, $userObject);
  if ($review['method'] != 'Hofstee') {
    updateDB($review, $mysqli);
  }
}
require '../include/std_set_menu.inc';
require '../include/toprightmenu.inc';

echo draw_toprightmenu(97);
?>
<div id="content">
<?php
echo $reviews_html;
$mysqli->close();
?>
</tbody>
</table>
</body>
</html>
