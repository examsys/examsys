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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';
  
  if (isset($_POST['ok']) or (isset($_POST['returnhit']) and $_POST['returnhit'] == '1')) {
    $new_keyword = trim($_POST['new_keyword']);
    if ($new_keyword != '') {
      $result = $mysqli->prepare("INSERT INTO keywords_user VALUES (NULL,$userObject->get_user_ID(),?,'personal')");
      $result->bind_param('s', $new_keyword);
      $result->execute();  
      $result->close();
      
      $keywordID = $mysqli->insert_id;
    }
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Add Keyword</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  
  <script>
    function updateKeywords() {
      var keywordNo = parseInt(window.opener.document.getElementById('keywordno').value);
      var oldHTML = window.opener.document.getElementById('keywordarea').innerHTML;
      var newHTML = "<div style=\"background-color:highlight; color:white\" id=\"divkeyword" + keywordNo + "\"><input type=\"checkbox\" onclick=\"toggle('divkeyword" + keywordNo + "')\" name=\"keyword" + keywordNo + "\" value=\"<?php echo $keywordID; ?>\" checked>&nbsp;<?php echo str_replace('"','&quot;',$_POST['new_keyword']); ?></div>" + oldHTML;
      window.opener.document.getElementById('keywordarea').innerHTML = newHTML;
      window.opener.document.getElementById('keywordno').value = parseInt(keywordNo + 1);
      window.opener.document.getElementById('thelist').value = ';<?php echo $keywordID; ?>' + window.opener.document.getElementById('thelist').value;
      window.close();
    }

  </script>
</head>
<?php
  if ($new_keyword != '') {
    echo "<body onload=\"updateKeywords();\">\n";
  } else {
    echo "<body onload=\"window.close();\">\n";
  }
?>
</body>
</html>
<?php
  } else {
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Add Keyword</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#EEEEEE}
    h1 {font-size:120%}
  </style>

  <script>
    function illegalChar(codeID) {
      if (codeID == 35) {
        alert("Character '#' illegal - please use alternative characters in keyword.");
        event.returnValue = false;
      } else if (codeID == 38) {
        alert("Character '&' illegal - please use alternative characters in keyword.");
        event.returnValue = false;
      } else if (codeID == 59) {
        alert("Character ';' illegal - please use alternative characters in keyword.");
        event.returnValue = false;
      } else if (codeID == 63) {
        alert("Character '?' illegal - please use alternative characters in keyword.");
        event.returnValue = false;
      } else if (codeID == 64) {
        alert("Character '@' illegal - please use alternative characters in keyword.");
        event.returnValue = false;
      } else if (codeID == 94) {
        alert("Character '^' illegal - please use alternative characters in keyword.");
        event.returnValue = false;
      } else if (codeID == 126) {
        alert("Character '~' illegal - please use alternative characters in keyword.");
        event.returnValue = false;
      } else if (codeID == 13) {
        document.myform.returnhit.value = '1';
        document.myform.submit();
      }
    }
  </script>

</head>

<body onload="document.myform.new_keyword.focus();">
<h1>New Keyword</h1>
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
<div><input type="text" style="width:100%" name="new_keyword" onkeypress="illegalChar(event.keyCode)" /></div>
<div align="right"><input type="submit" name="ok" value="OK" style="width:80px" />&nbsp;<input type="button" name="cancel" value="Cancel" style="width:80px" onclick="window.close();" /><input type="hidden" name="returnhit" value="" /></div>
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>