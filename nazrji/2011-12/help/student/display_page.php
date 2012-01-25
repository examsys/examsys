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
* Displays a page from the Staff online help.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


require '../../include/staff_student_auth.inc';

function getPath($path) {
  global $string;
  
  $parts = explode('/',$path);
  $path = '<a style="color:#666666" href="display_page.php?id=1">' . $string['home'] . '</a>';
  if (count($parts) > 1) {
    for ($i=0; $i<count($parts)-1; $i++) {
      $path .= " > <a style=\"color:#666666\" href=\"display_folder.php?title=" . $parts[$i] . "\">" . $parts[$i] . "</a>";
    }
  }    
  return $path;
}

function getTitle($path) {
  $parts = explode('/',$path);
  return $parts[count($parts)-1];
}

$search_results = $mysqli->prepare("SELECT title, body, type, deleted FROM student_help WHERE id=?");
$search_results->bind_param('i', $_GET['id']);
$search_results->execute();
$search_results->store_result();
$search_results->bind_result($tmp_title, $tmp_body, $type, $deleted);
while ($row = $search_results->fetch()) {
  $edit_id = $_GET['id'];
  if ($type == 'pointer') {
    $redirect_results = $mysqli->query("SELECT title, body, deleted FROM student_help WHERE id=$tmp_body");
    while ($redirect_row = $redirect_results->fetch_assoc()) {
      $edit_id = $tmp_body;
	  $tmp_body = $redirect_row['body'];
	  $deleted = $redirect_row['deleted'];
    }
    $redirect_results->close();
  }
}
$search_results->free_result();
$search_results->close();

if ($tmp_body == '' and $tmp_title == '') {
  header("HTTP/1.0 404 Not Found");
  exit;
}

if ($_GET['id'] != '1' and strpos($userroles,'SysAdmin') === false) {   // Don't record the homepage or SysAdmin activities.
  $result = $mysqli->prepare("INSERT INTO help_log VALUES (NULL,'student',?,NOW(),?)");
  $result->bind_param('ii', $userID, $_GET['id']);
  $result->execute();  
  $result->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Help and Support Center</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<style>
body {background-color:white; color:black; margin:0px; font-family:Arial,sans-serif; font-size:85%; line-height:150%}
p, div, td {color:#484848}
ul {list-style:square outside; color:#f27000}
table {font-size:100%}
h1 {font-size:150%; color:black}
h2 {font-size:140%; color:#f27000}
.path {background-color:#F2F2F2; color:#666666; font-size:80%; padding-left:10px; border-bottom:1px solid #B6B6B6}
.subheading {font-weight:bold; font-style:italic}
.tutorial {background-color:#FCF6CF; width: 95%; cursor:pointer}
</style>
<script language="JavaScript">
  function updateToolbar(editID,deleteID) {
    parent.frames['toolbar'].document.myform.editid.value=editID;
    parent.frames['toolbar'].document.myform.deleteid.value=deleteID;
    <?php
    if (isset($_GET['section'])) {
      echo "window.location='#" . $_GET['section'] . "'\">\n";
    }
    ?>
  }
  
  function updateTOC() {
    if (parent.frames['navigation'].document.getElementById('old_highlight').value != 0) {
      old_section = parent.frames['navigation'].document.getElementById('old_highlight').value;
      parent.frames['navigation'].document.getElementById(old_section).style.fontWeight="normal";
      parent.frames['navigation'].document.getElementById(old_section).style.textDecoration="none";
    }
    <?php
    if ($_GET['id'] != 1) {
      echo "parent.frames['navigation'].document.getElementById('title" . $_GET['id'] . "').style.fontWeight=\"bold\";\n";
      echo "parent.frames['navigation'].document.getElementById('title" . $_GET['id'] . "').style.textDecoration=\"underline\";\n";
    }
    ?>
    parent.frames['navigation'].document.getElementById('old_highlight').value = 'title<?php echo $_GET['id']; ?>';
  }
  
 function openTutorial(file) {
   var winheight = screen.height-30;
    var winwidth = screen.width-20;
    notice = window.open("./viewCaptivate.php?tutorial=" + file + "","Tutorial","width=" + winwidth + ",height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
    notice.moveTo(0,0);
    if (window.focus) {
      notice.focus();
    }  
 }
</script>
</head>

<?php
  
  if ($deleted != '') {
    echo "<body>\n<br />\n<p style=\"margin-left:15px\">" . $string['msg'] . "</p>\n</body>\n</html>\n";
    exit;
  }
  
  echo "<body onload=\"updateToolbar(" . $_GET['id'] . "," . $_GET['id'] . "); updateTOC();\">\n";
  
  if ($_GET['id'] == 1) {
    // ID 1 is for the homepage.
    echo "<div>\n";
  } else {
    echo "<a name=\"top\"></a>";
    echo "<div class=\"path\">" . getPath($tmp_title) . "</div>";
    echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#7598C4\">" . getTitle($tmp_title) . "</div>\n<hr style=\"width:100%; background-color:#B6B6B6; color:#B6B6B6; height:1px; border:0px\" />\n";
    echo "<div style=\"margin-left:20px; margin-right:20px\">\n";
  }
  
  $offset = 0;
  
  // Perform replacement on certain strings.
  $tmp_body = str_replace('$support_email', '<a href="mailto:' . $support_email . '">' . $support_email . '</a>', $tmp_body);
  $tmp_body = str_replace('$local_server', $protocol . $_SERVER['HTTP_HOST'], $tmp_body);
  
  if (isset($_GET['highlight'])) {
    do {
      $found = stripos($tmp_body, $_GET['highlight'], $offset);
      if ($found !== false) {
        $first_part = substr($tmp_body, 0 , $found);
        $open_bracket = strrpos($first_part, '<');
        $close_bracket = strrpos($first_part, '>');
        if (($open_bracket < $found and $found < $close_bracket) or ($close_bracket < $open_bracket)) {
          $offset = $found + strlen($_GET['highlight']);
        } else {
          $tmp_body = substr($tmp_body, 0, $found) . '<span style="background-color:#FFFF00">' . substr($tmp_body, $found, strlen($_GET['highlight'])) . '</span>' . substr($tmp_body, $found + strlen($_GET['highlight']));
          $offset = $found + 48;
        }
      }
    } while ($found !== false);
  }
  echo $tmp_body;
  if ($_GET['id'] > 1) {
    echo "<br clear=\"all\" />\n<hr style=\"width:100%; background-color:#B6B6B6; color:#B6B6B6; height:1px; border:0px; margin-bottom:5px\" />\n</div>\n";
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\"><tr>";
    echo "<td style=\"padding-left:20px\"><a style=\"color:#003366\" href=\"#top\"><img src=\"../../artwork/top_icon.gif\" width=\"9\" height=\"12\" border=\"0\" alt=\"" . $string['top'] . "\" /></a>&nbsp;<a style=\"color:#003366\" href=\"#top\">" . $string['top'] . "</a></td><td style=\"padding-right:20px; text-align:right\">&copy; 2011, The University of Nottingham</td></tr>";
    if (strpos($userroles,'SysAdmin') !== false) {
      echo '<tr><td colspan="2" style="padding-right:20px; text-align:right; color:#316AC5">' . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . '/help/student/index.php?id=' . $_GET['id'] . '</tr>';
    }
    echo "</table>\n";
  }
?>
</body>
</html>