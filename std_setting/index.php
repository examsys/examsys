<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

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

function displayReview($review, $userObj)
{
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
        $html .= "<tr id=\"review{$review['std_setID']}\" class=\"l\" data-id=\"" . $review['std_setID'] . '" data-setter="' . $setter_id . '" data-method="' . $review['method'] . "\" data-menu='menu2b' data-group=\"" . $review['group_review'] . "\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" /></td><td>&nbsp;";
    } else {
        $html .= "<tr id=\"review{$review['std_setID']}\" class=\"l\" data-id=\"" . $review['std_setID'] . '" data-setter="' . $setter_id . '" data-method="' . $review['method'] . "\" data-menu='menu2c' data-group=\"" . $review['group_review'] . "\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" /></td><td>&nbsp;";
    }
    if ($review['distinction_score'] != 'n/a') {
        $review['distinction_score'] .= '%';
    }
    if ($review['group_review'] != 'No') {
        $html .= '&lt;group review&gt;</a>';
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
  <title><?php echo page::title('ExamSys: ' . $string['listsettings']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src='../js/stdsetinit.min.js'></script>
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
if ($userObject->has_role('Standards Setter')) {
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
<?php
$render = new render($configObject);
$dataset['name'] = 'dataset';
$dataset['attributes']['datetime'] = $configObject->get('cfg_tablesorter_date_time');
$dataset['attributes']['paperid'] = $paperID;
$dataset['attributes']['module'] = $module;
$dataset['attributes']['folder'] = $folder;
$render->render($dataset, array(), 'dataset.html');
?>
</body>
</html>
