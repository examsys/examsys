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

require '../include/staff_auth.inc';

?>
<!DOCTYPE html
   PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>TouchStone</title>

<style type="text/css">
body {font-family: Arial, Helvetica, sans-serif; font-size: 90%; color: black; margin-top: 0px; margin-left: 0px; margin-right: 0px}
.heading {background-color: #EBEADB; border-left: solid white 1px; border-right: solid #D8D2BD 1px; border-top: solid white 1px; border-bottom: solid #D8D2BD 1px; color: black; font-family: Arial, Helvetica, sans-serif}
</style>

<script language="JavaScript" type="text/javascript">
  function confirmSubmit() {
    var agree = confirm("Are you sure you want to email everyone on this list their marks?");
    if (agree)
      return true;
    else
      return false;
  }
  
  function reviewPaper(started,userid,surname) {
    var winwidth = 750;
    var winheight = screen.height-80;
    window.open("../start.php?paper=<?php echo $paper ?>&previous="+started+"&userid="+userid+"&surname="+surname+"","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  }
</script>
</head>

<body>
<?php
  $question_data = mysql_query("SELECT DISTINCT username FROM log WHERE q_paper=\"" . $_GET['paper'] . "\" ORDER BY username;",$link_id);
  while ($row = mysql_fetch_array($question_data)) {
    print ("<div><a href=\"mark_textboxes.php?paper=" . $_GET['paper'] . "&username=" . $row["username"] . "\">" . $row["username"] . "</a></div>\n");
  }
?>

</body>
</html>
