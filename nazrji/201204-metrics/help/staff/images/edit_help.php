<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
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
    $tmp_body = ereg_replace("'","''",$tmp_body);//fix SQL

    $tmp_body_plain = strip_tags($tmp_body);

    $editQuery = "UPDATE student_help SET title=\"" . $_POST['title'] . "\", body='$tmp_body', body_plain='$tmp_body_plain' WHERE id=" . $_GET["id"];
    if (!mysql_query($editQuery, $link_id)) {
      echo "<p>" . mysql_error($link_id) . "</p>\n";
      echo "<p>$editQuery</p>\n";
      exit;
    }
    if ($_SERVER['SERVER_PORT'] == 443) {
      header("location: https://" . $_SERVER['HTTP_HOST'] . "/touchstone/student_help/display_page.php?id=" . $_GET['id']);
    } else {
      header("location: http://" . $_SERVER['HTTP_HOST'] . "/touchstone/student_help/display_page.php?id=" . $_GET['id']);
    }
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
    body {background-color:white; color:black; margin-left:30px; margin-right:30px; font-family:Arial,sans-serif; font-size:85%; line-height:150%}
    p, li {text-align: justify}
    td {font-size:85%}
    h1 {font-size:150%; color:black}
    h2 {font-size:130%; color:black}
    .subheading {font-weight:bold; font-style:italic}  </style>
  </style>
  <script language=JavaScript src='../editor/scripts/innovaeditor.js'></script>
  <script language="JavaScript">
    function getSize() {
      var frHeight = parent.document.getElementById("content").height;
      frHeight = frHeight - 155;
      return frHeight + 'px';
    }
  </script>
</head>

<body>

<form name="add_form" charset="UTF-8" method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']; ?>">
<?php
  $results = mysql_query("SELECT title, body, id FROM student_help WHERE id=" . $_GET['id'] . " LIMIT 1",$link_id);
  while ($row = mysql_fetch_array($results)) {
    echo "<p><input type=\"text\" style=\"font-family: Arial,sans-serif; font-size:150%; font-weight:bold\" size=\"50\" name=\"title\" value=\"" . $row['title'] . "\" /></p>\n";
    echo "<textarea name=\"body\" id=\"body\" cols=\"75\" rows=\"30\">" . encodeHTML(stripslashes($row['body'])) . "</textarea>";
    ?>
      <script>
	var oEdit1 = new InnovaEditor("oEdit1");
        oEdit1.mode="XHTMLBody";
        oEdit1.useTagSelector=false;
        oEdit1.useBR=false;
        oEdit1.width="100%";
        oEdit1.height=getSize();
        oEdit1.features=["Cut","Copy","PasteText","|","Undo","|","Bold","Italic","Underline","|","Superscript","Subscript","|","JustifyLeft","JustifyCenter","JustifyRight","|","Numbering","Bullets","|","Table","Characters","|","XHTMLSource"];
        oEdit1.arrStyle = [["BODY",false,"","background-color:white; color: black; margin-left:30px; margin-right:30px; font-family:Arial,sans-serif; font-size:85%; line-height:150%"],["h1",false,"","font-size: 150%; color: black"],["h2",false,"","font-size: 130%; color: black"],["p",false,"","text-align: justify"],["li",false,"","text-align: justify"],[".subheading",false,"","font-weight: bold; font-style: italic"]];
        oEdit1.btnStyles = true;
        oEdit1.REPLACE("body");
      </script>
    <?php
  }
?>

<div style="text-align:center"><input style="font-family:Arial,sans-serif; width:120px" type="submit" name="save_changes" value="Save" />&nbsp;&nbsp;<input style="font-family:Arial,sans-serif; width:120px" type="button" name="cancel" value="Cancel" onclick="history.back();" /></div>
</form>
</body>
</html>
<?php
  }
?>