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

require '../include/staff_auth.inc';

$paper = $_GET['paper'];
$startdate = $_GET['startdate'];
$enddate = $_GET['enddate'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō: Textbox Marking</title>

  <style type="text/css">
  body {font-family:Arial,sans-serif; font-size:90%; background-color:#9099AE; color:black; margin-top:6px; margin-bottom:20px; margin-left:40px; margin-right:40px}
  p {margin-left:30px; margin-top:10px; marging-bottom:10px; margin-right:30px; line-height:150%}
  a.user {font-family:Arial,sans-serif; color:black}
  a.user:hover {color:white; background-color:#000080}
  .heading {background-color:#EBEADB; color:black; font-family:Arial,sans-serif}
  </style>

  <script language="JavaScript" type="text/javascript">
    function resizeText() {
      selecteditem = document.getElementById('percentage').selectedIndex ;
      new_size = document.getElementById('percentage').options[ selecteditem ].value
      document.getElementById('preview').style.fontSize = new_size;
    }
  </script>
</head>

<body>
<select id="percentage" onchange="resizeText()">
<option value="90%">90%</option>
<option value="100%">100%</option>
<option value="120%" selected>120%</option>
<option value="150%">150%</option>
<option value="200%">200%</option>
<option value="300%">300%</option>
</select><br />
<table id="preview" cellpadding="20" cellspacing="0" border="0" style="font-size:120%; background-color:white; border:1px solid black; filter:progid:DXImageTransform.Microsoft.Shadow(color='black', Direction=135, Strength=2)">
<tr><td>
<?php
  $question_string = "SELECT user_answer FROM log WHERE id=" . $_GET['id']  . " ORDER BY id LIMIT 1";
  $question_data = mysql_query($question_string,$link_id);
  $row = mysql_fetch_array($question_data);
  echo "<p>" . $row['user_answer'] . "</p>\n";
?>
</td></tr>
</table>

</body>
</html>
