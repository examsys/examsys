<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  if (strpos($_SERVER['PHP_SELF'],'student_help') !== false) {
    $help_type = 'student';
    $require_file = '../include/staff_student_auth.inc';
  } else {
    $help_type = 'staff';
    $require_file = '../include/staff_auth.inc';
  }

  require $require_file;
  
  function getPath($path) {
    $parts = split("/",$path);
    $path = '<a style="color:#666666" href="display_page.php?id=1">TouchStone Home</a>';
    if (count($parts) > 1) {
      for ($i=0; $i<count($parts)-1; $i++) {
        $path .= " > <a style=\"color:#666666\" href=\"display_folder.php?title=" . $parts[$i] . "\">" . $parts[$i] . "</a>";
      }
    }    
    return $path;
  }
  
  function getTitle($path) {
    $parts = split("/",$path);
    return $parts[count($parts)-1];
  }
  
?>
<html>
<head>
<title>Help and Support Center</title>
<style>
body {background-color:white; color:black; margin:0px; font-family:Arial,sans-serif; font-size:85%; line-height:150%}
p, div, td {color:#484848}
ul {list-style:square outside; color:#f27000}
table {font-size:100%}
h1 {font-size:150%; color:black; font-family:Verdana,sans-serif}
h2 {font-size:140%; color:#f27000; font-family:Verdana,sans-serif}
.path {background-color:#F2F2F2; color:#666666; font-size:80%; padding-left:10px; border-bottom:1px solid #B6B6B6}
.subheading {font-weight:bold; font-style:italic}
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
    parent.frames['navigation'].document.getElementById('title<?php echo $_GET['id']; ?>').style.fontWeight="bold";
    parent.frames['navigation'].document.getElementById('title<?php echo $_GET['id']; ?>').style.textDecoration="underline";
    parent.frames['navigation'].document.getElementById('old_highlight').value = 'title<?php echo $_GET['id']; ?>';
  }
</script>
</head>

<?php
  if ($_GET['id'] != '1' and strpos($userroles,'SysAdmin') === false) {   // Don't record the homepage or SysAdmin activities.
    $result = $mysqli->prepare("INSERT INTO help_log VALUES (NULL,?,?,NOW(),?)");
    $result->bind_param('sii', $help_type, $userID, $_GET['id']);
    $result->execute();  
    $result->close();
  }
  
  $search_results = $mysqli->prepare("SELECT title, body, type FROM " . $help_type . "_help WHERE id=?");
  $search_results->bind_param('i', $_GET['id']);
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($tmp_title, $tmp_body, $type);
  while ($row = $search_results->fetch()) {
    $edit_id = $_GET['id'];
    if ($type == 'pointer') {
      $redirect_results = $mysqli->query("SELECT title, body FROM " . $help_type . "_help WHERE id=$tmp_body");
      while ($redirect_row = $redirect_results->fetch_assoc()) {
        $edit_id = $tmp_body;
        $tmp_body = $redirect_row['body'];
      }
      $redirect_results->close();
    }
  }
  $search_results->free_result();
  $search_results->close();
  
  echo "<body onload=\"updateToolbar(" . $_GET['id'] . "," . $_GET['id'] . "); updateTOC();\">\n";
  
  if ($_GET['id'] == 1) {
    // ID 1 is for the homepage.
    echo "<div>\n";
  } else {
    echo "<a name=\"top\"></a>";
    echo "<div class=\"path\">" . getPath($tmp_title) . "</div>";
    echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#7598C4; font-family:Verdana,sans-serif\">" . getTitle($tmp_title) . "</div>\n<hr noshade size=\"1\" width=\"100%\" style=\"color:#B6B6B6\" />\n";
    echo "<div style=\"margin-left:20px; margin-right:20px\">\n";
  }
  $offset = 0;
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
    echo "<br />\n<hr noshade size=\"1\" width=\"100%\" style=\"color:#C0C0C0\" />\n</div>\n";
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\"><tr>";
    echo "<td style=\"padding-left:20px\"><a style=\"color:#003366\" href=\"#top\"><img src=\"../artwork/top_icon.gif\" width=\"9\" height=\"12\" border=\"0\" alt=\"Top\" /></a>&nbsp;<a style=\"color:#003366\" href=\"#top\">Top of Page</a></td><td style=\"padding-right:20px; text-align:right\">&copy; 2010, The University of Nottingham</td></tr>";
    if (strpos($userroles,'SysAdmin') !== false) {
      echo '<tr><td colspan="2" style="padding-right:20px; text-align:right; color:#316AC5">' . $protocol . $_SERVER['HTTP_HOST'] . '/touchstone/' . $help_type . '_help/index.php?id=' . $_GET['id'] . '</tr>';
    }
    echo "</table>\n";
  }
?>
</body>
</html>