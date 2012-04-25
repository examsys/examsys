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
  } else {
    $help_type = 'staff';
  }

  require '../include/sysadmin_auth.inc';
  header('Content-Type: text/html; charset=UTF-8');

  function encodeHTML($sHTML) {
    $sHTML=ereg_replace("&","&amp;",$sHTML);
    $sHTML=ereg_replace("<","&lt;",$sHTML);
    $sHTML=ereg_replace(">","&gt;",$sHTML);
    return $sHTML;
  }

  if (isset($_POST['save_changes'])) {
    // Update help file record
    $tmp_body = stripslashes($_POST['body']);
    $tmp_body_plain = strip_tags($tmp_body);
    $order   = array("\r\n", "\n", "\r", "\t");
    $tmp_body_plain = str_replace($order,' ',$tmp_body_plain);
    $tmp_body_plain = str_replace('  ',' ',$tmp_body_plain);
    
    if ($_POST['edit_id'] == $_POST['original_id']) {
      // Editing normal page.
      $result = $mysqli->prepare("UPDATE " . $help_type . "_help SET title=?, body=?, body_plain=?, checkout_time=NULL, checkout_authorID=NULL WHERE id=?");
      $result->bind_param('sssi', stripslashes($_POST['title']), $tmp_body, $tmp_body_plain, $_POST['edit_id']);
      $result->execute();  
      $result->close();
    } else {
      // Editing a page pointed to.
      $result = $mysqli->prepare("UPDATE " . $help_type . "_help SET title=? WHERE id=?");
      $result->bind_param('si', stripslashes($_POST['title']), $_POST['original_id']);
      $result->execute();  
      $result->close();
      
      $result = $mysqli->prepare("UPDATE " . $help_type . "_help SET body=?, body_plain=?, checkout_time=NULL, checkout_authorID=NULL WHERE id=?");
      $result->bind_param('ssi', $tmp_body, $tmp_body_plain, $_POST['edit_id']);
      $result->execute();  
      $result->close();
    }
    
    
    $mysqli->close();
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/" . $help_type . "_help/display_page.php?id=" . $_POST['original_id']);
  } elseif (isset($_POST['cancel'])) {
    // Release authoring lock.
    if ($_POST['checkout_authorID'] == $userID) {
      $result = $mysqli->prepare("UPDATE " . $help_type . "_help SET checkout_time=NULL, checkout_authorID=NULL WHERE id=?");
      $result->bind_param('i', $_POST['edit_id']);
      $result->execute();  
      $result->close();
    }
    $mysqli->close();
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/" . $help_type . "_help/display_page.php?id=" . $_POST['original_id']);  
  } else {
?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <title>Edit Help File</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
  
  <script language=JavaScript src='/touchstone/staff_help/editor/scripts/innovaeditor.js'></script>
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
  $result = $mysqli->prepare("SELECT title, body, id, DATE_FORMAT(checkout_time,'%Y%m%d%H%i%S') AS checkout_time, checkout_authorID, type FROM " . $help_type . "_help WHERE id=? LIMIT 1");
  $result->bind_param('i', $_GET['id']);
  $result->execute();
  $result->bind_result($title, $body, $id, $page_checkout_time, $page_checkout_authorID, $type);
  $result->fetch();
  $result->close();

  if ($type == 'pointer') {
    $edit_id = $body;
    $redirect_results = $mysqli->query("SELECT body FROM " . $help_type . "_help WHERE id=$body");
    while ($redirect_row = $redirect_results->fetch_assoc()) {
      $body = $redirect_row['body'];
    }
    $redirect_results->close();
  } else {
    $edit_id = $_GET['id'];
  }

  echo "<p style=\"margin-left:20px\"><input type=\"text\" style=\"font-family:Verdana,sans-serif; color:#7598C4; font-size:160%; border: 1px solid #C0C0C0; font-weight:bold\" size=\"50\" name=\"title\" value=\"$title\" /></p>\n";
  echo "<textarea name=\"body\" id=\"body\" cols=\"75\" rows=\"30\">" . encodeHTML($body) . "</textarea>";
  ?>
  <script>
    var oEdit1 = new InnovaEditor("oEdit1");
    oEdit1.mode="XHTMLBody";
    oEdit1.useTagSelector=false;
    oEdit1.useBR=false;
    oEdit1.width="100%";
    oEdit1.height=getSize();
    oEdit1.arrCustomButtons=[['UploadImage','window.open("./addImage.php?<?php echo str_replace('\'','%27',$_SERVER['QUERY_STRING']); ?>",500,300)','Add Image','btnImage.gif']];
    oEdit1.features=["Paragraph","Cut","Copy","PasteText","PasteWord","|","Undo","|","Bold","Italic","Underline","|","ForeColor","|","Superscript","Subscript","|","JustifyLeft","JustifyCenter","JustifyRight","JustifyFull","|","Numbering","Bullets","|","Hyperlink","UploadImage","Table","Characters","|","XHTMLSource"];
    oEdit1.arrStyle = [["BODY",false,"","background-color:white; color:black; margin-left:20px; margin-right:20px; font-family:Arial,sans-serif; font-size:85%; line-height:150%"],["h1",false,"","font-size:150%; color:black; font-family:Verdana,sans-serif"],["h2",false,"","font-size:130%; color:#f27000; font-family:Verdana,sans-serif"],["p",false,"","color:#484848"],["td",false,"","color:#484848"],["div",false,"","color:#484848"],["ul",false,"","list-style:square outside; color:#FF9900"],["table",false,"","font-size:100%"],[".subheading",false,"","font-weight:bold; font-style:italic"]];
    oEdit1.btnStyles = true;
    oEdit1.REPLACE("body");
  </script>

  <?php
    // Check for lockout.
    $current_time = date('YmdHis');
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
        $result = $mysqli->prepare("UPDATE " . $help_type . "_help SET checkout_time=NOW(), checkout_authorID=? WHERE id=?");
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