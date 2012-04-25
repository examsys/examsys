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

  require '../include/staff_auth.inc';    // Only let staff create pages.
  header('Content-Type: text/html; charset=UTF-8');

  function encodeHTML($sHTML) {
    $sHTML=ereg_replace("&","&amp;",$sHTML);
    $sHTML=ereg_replace("<","&lt;",$sHTML);
    $sHTML=ereg_replace(">","&gt;",$sHTML);
    return $sHTML;
  }

  if (isset($_POST['save_changes'])) {
    $tmp_title = stripslashes($_POST['title']);
    // Check to see if dummy parent record exists for title.
    if (strpos($tmp_title,'/') !== false) {
      $parts = split('/',$tmp_title);
      $parent = $parts[0];
      
      $result = $mysqli->prepare("SELECT id FROM " . $help_type . "_help WHERE title=?");
      $result->bind_param('s', $parent);
      $result->execute();
      $result->bind_result($help_pageID);
      $result->fetch();
      $result->close();
    
      if ($help_pageID == '') {
        $result = $mysqli->prepare("INSERT INTO " . $help_type . "_help VALUES (NULL,?,'','','page',NULL,NULL)");
        $result->bind_param('s', $parent);
        $result->execute();  
        $result->close();
      }
    }
  
    // Update help file record
    $tmp_body = stripslashes($_POST['body']);
    $tmp_body_plain = strip_tags($tmp_body);

    $result = $mysqli->prepare("INSERT INTO " . $help_type . "_help VALUES (NULL,?,?,?,'page',NULL,NULL)");
    $result->bind_param('sss', $tmp_title, $tmp_body, $tmp_body_plain);
    $result->execute();  
    $result->close();

    $page_id = $mysqli->insert_id;
    $mysqli->close();
    ?>
    <html>
    <head>
    <title></title>
    <script language="JavaScript">
      function reloadHelp() {
        window.top.location='/touchstone/<?php echo $help_type; ?>_help/index.php?id=<?php echo $page_id; ?>';
      }
    </script>
    </head>
    <body onload="reloadHelp()">
    </body>
    </html>
    
    <?php
  } else {
?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <title>New Help Page</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <style>
    html {height:100%}
    body {background-color:white; color:black; margin-left:0px; margin-right:0px; font-family:Arial,sans-serif; font-size:85%; line-height:150%; color:#484848}
    p, div, td {text-align:justify}
    li {text-align:justify; list-style:square inside; color:#FF9900}
    td {font-size:85%}
    h1 {font-size:150%; color:black; font-family:Verdana,sans-serif}
    h2 {font-size:140%; color:#EEA752; font-family:Verdana,sans-serif}
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
    
    function cleartext() {
      if (document.add_form.title.value == "Page Title...") {
        document.add_form.title.value = '';
      }
    }
    
    function checkForm() {
      if (document.add_form.title.value == "" || document.add_form.title.value == " ") {
        alert ("Please enter a title for this new help page.");
        return false;
      }
    }
    
  </script>
</head>

<body>

<form name="add_form" charset="UTF-8" method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']; ?>" onsubmit="return checkForm();">
  <p style="margin-left:20px"><input type="text" style="font-family:Verdana,sans-serif; color:#7598C4; font-size:160%; border:1px solid #C0C0C0; font-weight:bold" size="50" name="title" value="Page Title..." onfocus="cleartext();" /></p>
  <textarea name="body" id="body" cols="75" rows="30"></textarea>
  <script>
    var oEdit1 = new InnovaEditor("oEdit1");
    oEdit1.mode="XHTMLBody";
    oEdit1.useTagSelector=false;
    oEdit1.useBR=false;
    oEdit1.width="100%";
    oEdit1.height=getSize();
    oEdit1.arrCustomButtons=[['UploadImage','window.open("./addImage.php?<?php echo str_replace('\'','%27',$_SERVER['QUERY_STRING']); ?>",500,300)','Add Image','btnImage.gif']];
    oEdit1.features=["Cut","Copy","PasteText","PasteWord","|","Undo","|","Bold","Italic","Underline","|","ForeColor","|","Superscript","Subscript","|","JustifyLeft","JustifyCenter","JustifyRight","JustifyFull","|","Numbering","Bullets","|","UploadImage","Table","Characters","|","XHTMLSource"];
    oEdit1.arrStyle = [["BODY",false,"","background-color:white; color:black; margin-left:20px; margin-right:20px; font-family:Arial,sans-serif; font-size:85%; line-height:150%"],["h1",false,"","font-size:150%; color:black; font-family:Verdana,sans-serif"],["h2",false,"","font-size:130%; color:#EEA752; font-family:Verdana,sans-serif"],["p",false,"","text-align:justify; color:#484848"],["div",false,"","text-align:justify; color:#484848"],["li",false,"","text-align:justify; list-style:square outside; color:#FF9900"],[".subheading",false,"","font-weight: bold; font-style: italic"]];
    oEdit1.btnStyles = true;
    oEdit1.REPLACE("body");
  </script>

  <div style="text-align:center; padding-top:8px""><input style="font-family:Arial,sans-serif; width:120px" type="submit" name="save_changes" value="Save" />&nbsp;&nbsp;<input style="font-family:Arial,sans-serif; width:120px" type="button" name="cancel" value="Cancel" onclick="history.back();" /></div>
</form>
</body>
</html>
<?php
  }
?>