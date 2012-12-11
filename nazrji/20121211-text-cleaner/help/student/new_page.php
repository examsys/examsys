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

  require '../../include/sysadmin_auth.inc';    // Only let SysAdmin staff create pages.
  require '../../include/errors.inc';
  require '../../include/help.inc';

  header('Content-Type: text/html; charset=' . {$configObject->get('cfg_page_charset')});

  if (isset($_POST['save_changes'])) {
    $tmp_title = $_POST['title'];
      
    // Update help file record
    $tmp_body = $_POST['edit1'];
    $tmp_body_plain = strip_tags($tmp_body);

    $result = $mysqli->prepare("INSERT INTO student_help VALUES (NULL,?,?,?,'page',NULL,NULL,NULL)");
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
        window.top.location='index.php?id=<?php echo $page_id; ?>';
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
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>">

  <title>New Help Page</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    html {height:100%}
    body {font-size:85%; line-height:150%; color:#484848}
    p, div, td {text-align:justify}
    li {text-align:justify; list-style:square inside; color:#FF9900}
    td {font-size:85%}
    h1 {font-size:150%; color:black; font-family:Verdana,sans-serif}
    h2 {font-size:140%; color:#EEA752; font-family:Verdana,sans-serif}
    .subheading {font-weight:bold; font-style:italic}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script language="JavaScript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script language="JavaScript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_config_help_staff.js"></script>
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

<form name="add_form" charset="UTF-8" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return checkForm();">
  <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
  <tr>
  <td style="padding-left:20px"><input type="text" style="font-family:Verdana,sans-serif; color:#7598C4; font-size:160%; border:1px solid #C0C0C0; font-weight:bold" size="50" name="title" value="Page Title..." onfocus="cleartext();" /></td>
  </tr>
  </table>
  <br />

  <textarea class="mceEditor" id="edit1" name="edit1" style="width:100%; height:500px"></textarea>

  <div style="text-align:center; padding-top:8px""><input style="width:120px" type="submit" name="save_changes" value="Save" />&nbsp;&nbsp;<input style="width:120px" type="button" name="cancel" value="Cancel" onclick="history.back();" /></div>
</form>
</body>
</html>
<?php
  }
?>