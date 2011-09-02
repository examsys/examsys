<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require './include/staff_auth.inc';
  
  if (isset($_POST['delete'])) {
  $question_details = mysql_query("SELECT q_id, keywords FROM questions WHERE keywords LIKE \"%" . $_POST['deleteword'] . "%\" AND ownerID=$userID",$link_id);
  while ($row = mysql_fetch_array($question_details)) {
    $keyword_list = explode(';',$row['keywords']);
    $new_keyword_list = $row['keywords'];
    $new_keyword_list = preg_replace('/^' . $_POST['deleteword'] . ';/', '', $new_keyword_list);
    $new_keyword_list = preg_replace('/;' . $_POST['deleteword'] . ';/', ';', $new_keyword_list);
    $new_keyword_list = preg_replace('/;' . $_POST['deleteword'] . '$/', '', $new_keyword_list);
    $new_keyword_list = preg_replace('/^' . $_POST['deleteword'] . '$/', '', $new_keyword_list);
    
    if (substr($new_keyword_list,0,1) == ';') $new_keyword_list = substr($new_keyword_list,1);
    echo "<div>UPDATE questions SET keywords=\"$new_keyword_list\" WHERE q_id=" . $row['q_id'] . "</div>\n";
  }
    exit;
?>
<html>
<head>
<title>Delete Keyword</title>
<script language="JavaScript">
</script>
</head>
<body>
</body>
</html>
<?php
  } else {
?>
<html>
<head>
<title>Delete Keyword</title>
<script language="JavaScript">

</script>
</head>

<body style="background-color:#ECE9D8; color:black; font-family:Arial,sans-serif" onload="document.myform.new_keyword.focus();">
<form name="myform" action="" method="post">
<div>
<select name="deleteword">
<?php
  $keyword_details = mysql_query("SELECT DISTINCT keyword FROM keywords WHERE userID=$userID ORDER BY keyword",$link_id);
  while ($row = mysql_fetch_array($keyword_details)) {
    echo "<option value=\"" . $row['keyword'] . "\">" . $row['keyword'] . "</option>\n";
  }
?>
</select></div>
<div align="right"><input type="submit" name="delete" value="Delete" style="width:80px" />&nbsp;<input type="button" name="cancel" value="Cancel" style="width:80px" onclick="window.close();" /></div>
</form>

</body>
</html>
<?php
}
?>