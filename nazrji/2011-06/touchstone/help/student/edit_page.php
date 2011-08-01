<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../../include/sysadmin_auth.inc';
  require '../../include/errors.inc';
  header('Content-Type: text/html; charset=UTF-8');
  
  if (isset($_POST['save_changes'])) {
    // Update help file record
    $tmp_body = $_POST['edit1'];
    $tmp_body_plain = strip_tags($tmp_body);
    $order   = array("\r\n", "\n", "\r", "\t");
    $tmp_body_plain = str_replace($order,' ',$tmp_body_plain);
    $tmp_body_plain = str_replace('  ',' ',$tmp_body_plain);
    $tmp_title = $_POST['page_title'];
    
    if ($_POST['edit_id'] == $_POST['original_id']) {
      // Editing normal page.
      $result = $mysqli->prepare("UPDATE student_help SET title=?, body=?, body_plain=?, checkout_time=NULL, checkout_authorID=NULL WHERE id=?");
      $result->bind_param('sssi', $tmp_title, $tmp_body, $tmp_body_plain, $_POST['edit_id']);
      $result->execute();  
      $result->close();
    } else {
      // Editing a page pointed to.
      $result = $mysqli->prepare("UPDATE student_help SET title=? WHERE id=?");
      $result->bind_param('si',$_POST['title'], $_POST['original_id']);
      $result->execute();  
      $result->close();
      
      $result = $mysqli->prepare("UPDATE student_help SET body=?, body_plain=?, checkout_time=NULL, checkout_authorID=NULL WHERE id=?");
      $result->bind_param('ssi', $tmp_body, $tmp_body_plain, $_POST['edit_id']);
      $result->execute();  
      $result->close();
    }
        
    $mysqli->close();
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/help/student/display_page.php?id=" . $_POST['original_id']);
  } elseif (isset($_POST['cancel'])) {
    // Release authoring lock.
    if ($_POST['checkout_authorID'] == $userID) {
      $result = $mysqli->prepare("UPDATE student_help SET checkout_time=NULL, checkout_authorID=NULL WHERE id=?");
      $result->bind_param('i', $_POST['edit_id']);
      $result->execute();  
      $result->close();
    }
    $mysqli->close();
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/help/student/display_page.php?id=" . $_POST['original_id']);  
  } else {
?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <title>Edit Help File</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <style>
    html {height:100%}
    body {background-color:white; color:black; margin-left:0px; margin-right:0px; font-family:Arial,sans-serif; font-size:85%; line-height:150%; color:#484848}
    p, div, td {color:#484848}
    ul {list-style:square outside; color:#FF9900}
    td {font-size:85%}
    h1 {font-size:150%; color:black; font-family:Verdana,sans-serif}
    h2 {font-size:140%; color:#f27000; font-family:Verdana,sans-serif}
    .subheading {font-weight:bold; font-style:italic}
  </style>
  
  <script language="JavaScript" src="/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script language="JavaScript" src="/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_config_help_student.js"></script>
  <script language="JavaScript">
    function getSize() {
      if (parseInt(navigator.appVersion)>3) {
        if (navigator.appName=="Netscape") {
          winH = window.innerHeight;
        }
        if (navigator.appName.indexOf("Microsoft")!=-1) {
          winH = parent.document.getElementById("content").height;
        }
      }
      winH = winH - 155;
      return winH + 'px';
    }
    
    function checkForm() {
      if (document.add_form.title.value == "" || document.add_form.title.value == " ") {
        alert ("Please enter a title for this help page.");
        return false;
      }
    }

  </script>
</head>

<body>

<form name="add_form" charset="UTF-8" method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']; ?>" onsubmit="return checkForm();">
<?php
  $result = $mysqli->prepare("SELECT title, body, id, DATE_FORMAT(checkout_time,'%Y%m%d%H%i%S') AS checkout_time, checkout_authorID, type FROM student_help WHERE id=? LIMIT 1");
  $result->bind_param('i', $_GET['id']);
  $result->execute();
  $result->bind_result($page_title, $body, $id, $page_checkout_time, $page_checkout_authorID, $type);
  $result->fetch();
  $result->close();

  if ($type == 'pointer') {
    $edit_id = $body;
    $redirect_results = $mysqli->query("SELECT body FROM student_help WHERE id=$body");
    while ($redirect_row = $redirect_results->fetch_assoc()) {
      $body = $redirect_row['body'];
    }
    $redirect_results->close();
  } else {
    $edit_id = $_GET['id'];
  }

  echo "<p style=\"margin-left:20px\"><input type=\"text\" style=\"font-family:Verdana,sans-serif; color:#7598C4; font-size:160%; border: 1px solid #C0C0C0; font-weight:bold\" size=\"50\" name=\"page_title\" value=\"$page_title\" /></p>\n";
  echo "<textarea class=\"mceEditor\" id=\"edit1\" name=\"edit1\" style=\"width:100%; height:500px\">" . $body . "</textarea>\n";

  // Check for lockout.
  $current_time = date('YmdHis');
  $disabled = '';
  if ($userID != $page_checkout_authorID) {
    if ($page_checkout_time != '' and $current_time - $page_checkout_time < 10000) {
      $editor = $mysqli->prepare("SELECT title, initials, surname FROM users WHERE id=?");
      $editor->bind_param('i', $page_checkout_authorID);
      $editor->execute();
      $editor->bind_result($title, $initials, $surname);
      $editor->fetch();
      $editor->close();
      echo "<script language=\"JavaScript\">\n";
      echo "  alert('This page is currently locked for editing by $title $initials $surname. It is now in read only mode.')";
      echo "</script>\n";
      $checkout_authorID = $page_checkout_authorID;
      $disabled = ' disabled';
    } else {
      // Set the lock to the current time/author.
      $result = $mysqli->prepare("UPDATE student_help SET checkout_time=NOW(), checkout_authorID=? WHERE id=?");
      $result->bind_param('ii', $userID, $edit_id);
      $result->execute();  
      $result->close();
      $checkout_authorID = $userID;
    }
  } elseif ($disabled == '' and $userID == $page_checkout_authorID) {
    $checkout_authorID = $userID;
  }
?>
  <input type="hidden" name="checkout_authorID" value="<?php echo $checkout_authorID; ?>" />
  <div style="text-align:center; padding-top:8px"><input style="font-family:Arial,sans-serif; width:120px" type="submit" name="save_changes" value="Save"<?php echo $disabled; ?> />&nbsp;&nbsp;<input style="font-family:Arial,sans-serif; width:120px" type="submit" name="cancel" value="Cancel" /></div>
  <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>" /><input type="hidden" name="original_id" value="<?php echo $_GET['id']; ?>" />
</form>
</body>
</html>
<?php
  $mysqli->close();
  }
?>