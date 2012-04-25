<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  function check4Images($html,&$images) {
    if (stripos($html, '<img') !== false) {
      $img_parts = split('src="',$html);
      for ($i=1; $i<count($img_parts); $i++) {
        $quote_parts = split('"',$img_parts[$i]);
        $images[] = $quote_parts[0];
      }
    }
  }

  if (strpos($_SERVER['PHP_SELF'],'student_help') !== false) {
    $help_type = 'student';
    $path = '/var/www/touchstone/touchstone/student_help';
  } else {
    $help_type = 'staff';
    $path = '/var/www/touchstone/touchstone/staff_help';
  }
  
  require '../include/sysadmin_auth.inc';    // Only let staff delete pages.
  require '../include/errors.inc';
  header('Content-Type: text/html; charset=UTF-8');
  $image_list = array();

  // Is the current page real or a pointer.
  $results = $mysqli->query("SELECT type, body FROM " . $help_type . "_help WHERE id = " . $_GET['id']);
  $row = $results->fetch_assoc();
  $results->close();
  $type = $row['type'];
  $body = $row['body'];
  check4Images($body,$image_list);
  
  if (count($image_list) > 0) {
    foreach ($image_list as $filename) {
      // Check to see if the image is used on any other help pages.
      $results = $mysqli->query("SELECT id FROM " . $help_type . "_help WHERE body LIKE '%$filename%' AND id != " . $_GET['id']);
      if ($results->num_rows == 0) {
        // No records found - delete file
        $target = $path . substr($filename,1);
        unlink($target);
      }
      $results->close();
    }
  }

  if ($type == 'page') {
    // Search for any pointers to the current page.
    $results = $mysqli->query("SELECT id, body FROM " . $help_type . "_help WHERE type='pointer' AND id != " . $_GET['id'] . " AND body=" . $_GET['id']);
    while ($row = $results->fetch_assoc()) {
      $deleteQuery = "DELETE FROM " . $help_type . "_help WHERE id=" . $row['id'];
      if (!$mysqli->query($deleteQuery)) {
        display_error("Error deleting from '" . $help_type . "_help' table.",$mysqli->error);
      }
    }
  }
  
  $deleteQuery = "DELETE FROM " . $help_type . "_help WHERE id=" . $_GET['id'];
  if (!$mysqli->query($deleteQuery)) {
    display_error("Error deleting from '" . $help_type . "_help' table.",$mysqli->error);
  }
  $mysqli->close();
?>
<html>
<head>
<title>Delete Page</title>
<script language="JavaScript">
  function reloadHelp() {
    window.top.location='/touchstone/<?php echo $help_type; ?>_help/index.php';
  }
</script>
</head>
<body onload="reloadHelp()">

</body>
</html>