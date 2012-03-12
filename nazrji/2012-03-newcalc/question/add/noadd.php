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

  require '../../include/staff_auth.inc';
  
  function englishDate($orig_date) {
    $tmp_date = substr($orig_date,6,2);
    $tmp_date .= '/' . substr($orig_date,4,2);
    $tmp_date .= '/' . substr($orig_date,0,4);
    $tmp_date .= ' ' . substr($orig_date,8,2);
    $tmp_date .= ':' . substr($orig_date,10,2);
    return $tmp_date;
  }
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>My Questions</title>
  <script language="javascript">
    function paperProperties() {
      notice=window.open("../../paper/properties.php?paperID=<?php echo $_GET['paperID']; ?>&noadd=y","properties","width=827,height=510,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }
  </script>
</head>

<body style="background-color:#EEEEEE; font-family:Arial,sans-serif; font-size:90%">
<div align="center">
<form>
<table cellpadding="0" cellspacing="5" border="0" style="width:570px font-size:100%">
<tr><td width="40"><img src="../../artwork/orange_alert_48.png" width="48" height="48" border="0" alt="Exclamation Icon" /></td>
<td style="font-size: 160%; font-weight: bold">Warning: Active Paper</td></tr>
<tr><td></td><td><hr width="100%" style="border: 1px black solid" size="1" /></td></tr>
<tr><td>&nbsp;</td><td><span style="color: red; font-weight: bold"><?php echo englishDate($_GET['start_date']); ?></span> to <span style="color: red; font-weight: bold"><?php echo englishDate($_GET['end_date']); ?></span>.</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>New questions may not be added to this paper while it is currently active.</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>This is a safety feature to stop papers being accidentally modified while examinees may be taking them.</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td><strong>Solution:</strong><br />Open <a href="#" onclick="paperProperties(); return false;"><img src="../../artwork/small_link.png" width="12" height="12" alt="Shortcut" border="0" /></a>&nbsp;<a href="#" style="color: blue" onclick="paperProperties(); return false;">Edit Properties</a> from the 'Current Paper Tasks' pane and alter the available dates.</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" style="width: 120px" value="Cancel" onclick="window.close();" /></td></tr>
</table>
</form>
</div>
</body>
</html>