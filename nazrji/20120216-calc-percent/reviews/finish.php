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
require '../include/reviews.inc';
require '../include/errors.inc';

check_var('id', 'GET', true, false);

if ($stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid=?")) {
  $stmt->bind_param('i',$userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
}
$stmt->close();
  
$screen_data = array();
$stmt = $mysqli->prepare("SELECT property_id, paper_title, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, DATE_FORMAT(external_review_deadline,\"%Y%m%d\") AS external_review_deadline, DATE_FORMAT(internal_review_deadline,\"%Y%m%d\") AS internal_review_deadline FROM properties WHERE crypt_name=?");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $external_review_deadline, $internal_review_deadline);
$stmt->fetch();
$stmt->close();

if ($bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
if ($fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
if ($textsize == 'NULL' or $textsize == '') $textsize = 90;
if ($marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
if ($themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
if ($labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;

if ($userroles == 'External Examiner') {
  $review_type = 'External';
  $external_review_deadline = $external_review_deadline;
} else { 
  $review_type = 'Internal';
  $external_review_deadline = $internal_review_deadline; //this is to fix internal reviews did not know where else $external_review_deadline was used!!
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Rogō</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">

<style type="text/css">
  body {background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; padding:0px; margin:0px; border:0px; font-family:Arial,sans-serif; font-size:<?php echo $textsize; ?>%}
  li {margin-left:15px; margin-right:15px; font-family:Arial,sans-serif; font-size:100%}
  select, input {font-size:100%}
  blockquote {font-size:90%}
  table {font-size:100%}
  .paper {margin-left:0px; font-family:Arial,sans-serif; font-size:180%; color:white; font-weight:bold}
</style>

<script src="../js/ie_fix.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
  window.history.go(1);

  function refreshparent() {
    window.opener.location.reload();
  }
</script>
</head>

<body oncontextmenu="return false;" onload="refreshparent()">
<?php
  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #164994; background-color:#2765AB; background-image:url(\'../artwork/title_gradient.png\'); background-repeat:repeat-y; background-position:center">';
  echo '<tr><td class="raised_tbl"><div class="paper">' . $paper_title . '</div></td><td align="center" class="raised_tbl" width="50"><img src="../artwork/uni_logo.png" width="160" height="67" alt="University Logo" border="0" /></td></tr>';
  echo '</table>';
  
  if ($_POST['old_screen'] != '' and date("Ymd") <= $external_review_deadline) {  
    record_comments($property_id, $_POST['old_screen'], $mysqli, $_POST, $userID, $review_type);
  } else {
    echo "Deadline = $external_review_deadline";
  }
  echo '<blockquote>';
  if ($language == 'en') {
    echo '<p style="font-size:450%;font-family:\'Monotype Corsiva\',Rage,\'Brush Script MT\',\'Lucida Handwriting\',sans-serif">' . $string['thankyou'] . '</p>';
  } else {
    // Do not use fancy fonts for foreign lanuages due to extended character support issues.
    echo '<p style="font-size:450%">' . $string['thankyou'] . '</p>';
  }
  echo '</blockquote>';
  echo '<div style="text-align:center; border: 1px black solid; padding:10px; margin-left:100px; margin-right:100px" align="center"><input type="button" name="close" value="&nbsp;Close Window&nbsp;" onclick="window.close();" /></div>';

  $mysqli->close();
?>
</body>
</html>