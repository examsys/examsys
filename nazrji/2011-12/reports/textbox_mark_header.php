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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/errors.inc';
  
  $paperID = $_GET['paperID'];
  $q_id = $_GET['q_id'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Textbox Marking</title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
td {line-height:150%}
.heading {background-color:#EBEADB; color:black}
</style>

<script src="../javascript/ie_fix.js" type="text/javascript"></script>
<script language="JavaScript">
  function move_in(img_name) {
    document[img_name].src=onImg.src;
  }

  function move_out(img_name) {
    document[img_name].src=offImg.src;
  }
  
  onImg = new Image;
  onImg.src = '../artwork/up_folder_icon_on.gif';
  offImg = new Image;
  offImg.src = '../artwork/up_folder_icon_off.gif';
</script>
</head>

<body style="margin:0px">
<?php
  // Get some paper properties
  if ($result = $mysqli->prepare("SELECT paper_type, paper_title FROM properties WHERE property_id=?")) {
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($paper_type, $paper);
    $result->fetch();
    $result->close();
  } else {
    display_error("Properties Query Error",$mysqli->close());
  }
  
  // Get the marks for the question
  if ($result = $mysqli->prepare("SELECT leadin, correct_fback, marks FROM (questions, options) WHERE questions.q_id=options.o_id AND o_id=? LIMIT 1")) {
    $result->bind_param('i', $q_id);
    $result->execute();
    $result->bind_result($leadin, $feedback, $marks);
    $result->fetch();
    $result->close();
  } else {
    display_error("Properties Query Error",$mysqli->close());
  }
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n<tr><td class=\"heading\" colspan=\"10\"><span style=\"font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold\"><a onmouseover=\"move_in('image1')\" onmouseout=\"move_out('image1')\" href=\"textbox_select_q.php?paperID=" . $_SERVER['QUERY_STRING'] . "\" target=\"_top\"><img name=\"image1\" src=\"../artwork/up_folder_icon_off.gif\" style=\"vertical-align:middle\" width=\"32\" height=\"38\" alt=\"Up\" border=\"0\" /></a>&nbsp;Textbox Marking - Question " . $_GET['qNo'] . ".</span></td></tr>\n";
  echo "<tr style=\"height:4px\"><td valign=\"top\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n</table>\n";

  $mysqli->close();
?>
<table cellspacing="6" cellspacing="0" border="0">
<tr><td valign="top" width="40%" style="padding:10px; background-color:#FFFFC0; line-height:150%"><strong>Question</strong><br /><?php echo $leadin . '<br /><br />' . $marks; ?> marks</td><td valign="top" width="60%" style="padding:10px; background-color:#FFFFC0; line-height:150%"><strong>Model Answer</strong><br /><?php echo $feedback; ?></td></tr>

</body>
</html>
