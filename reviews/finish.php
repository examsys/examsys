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
require '../include/errors.php';

check_var('id', 'GET', true, false, false);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);
  
/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);

$paperID    = $propertyObj->get_property_id();
$paper_type	= $propertyObj->get_paper_type();

if ($userObject->has_role('External Examiner')) {
  $review_type = 'External';
	$review_deadline = strtotime($propertyObj->get_external_review_deadline());
} else {
  $review_type = 'Internal';
	$review_deadline = strtotime($propertyObj->get_internal_review_deadline());
}

$userid = $userObject->get_user_ID();

$review = new Review($paperID, $userid, $review_type, $mysqli);

if (isset($_POST['close'])) {
  $review->record_general_comments($_POST['paper_comments'], false);
  echo close_window();
  exit();
} elseif (isset($_POST['finish'])) {
  $review->record_general_comments($_POST['paper_comments'], true);
  echo close_window();
  exit(); 
}

function close_window() {
  $html = "<html>\n<head>\n<title>Rog&#333;</title>\n</head>\n<body onload=\"window.close(); window.opener.location.reload();\"></body>\n</html>";
  
  return $html;
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <meta http-equiv="imagetoolbar" content="no">
  <meta http-equiv="imagetoolbar" content="false">
  
  <title>Rog&#333;</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; font-size:<?php echo $textsize; ?>%}
    li {margin-left:15px; margin-right:15px; font-size:100%}
    blockquote {font-size:90%}
    .paper {font-size:180%; color:white; font-weight:bold}
  </style>

  <script>
    window.history.go(1);
  </script>
</head>

<body oncontextmenu="return false;">
  <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?id=<?php echo $_GET['id'] ?>" autocomplete="off">
<?php
  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  echo '<tr><td><div class="paper">' . $propertyObj->get_paper_title() . '</div><div style="color:white; font-weight:bold">' . $string['reviewcomplete'] . '</div></td><td align="center" class="raised_tbl" width="50"><img src="../config/logo.png" width="160" height="67" alt="University Logo" /></td></tr>';
  echo '</table>';

  $configObject = Config::get_instance();
  $start_of_day_ts = strtotime('midnight');

  if ($_POST['old_screen'] != '' and $start_of_day_ts <= $review_deadline) {
    $review->record_comments($_POST['old_screen']);
  } else {
    echo $string['deadline'] . ' = ' . date($configObject->get('cfg_long_date_php'), $review_deadline);
  }
  ?>
  <blockquote>
    <h1><?php echo $string['generalcomments'] ?></h1>
    <p><?php echo $string['generalmsg'] ?></p>
    <textarea name="paper_comments" width="80" rows="6" style="width:100%"><?php echo $review->get_paper_comments() ?></textarea>
  
  </blockquote>
  <div style="text-align:center"><input type="submit" name="close" value="<?php echo $string['saveclose'] ?>" class="ok" /><input type="submit" name="finish" value="<?php echo $string['savefinish'] ?>" class="ok" /></div>
<?php
  $mysqli->close();
?>
  </form>
</body>
</html>