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
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['noadd']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
	<style type="text/css">
    body {background-color:#EAEAEA; font-size:90%; margin:10px}
    .date {color:#C00000; font-weight: bold}
  </style>
  
  <script language="javascript">
    function paperProperties() {
      notice=window.open("../../paper/properties.php?paperID=<?php echo $_GET['paperID']; ?>&caller=details&noadd=y","properties","width=882,height=650,left="+(screen.width/2-325)+",top="+(screen.height/2-441)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }
  </script>
</head>

<body>
<form>
<table cellpadding="0" cellspacing="5" border="0" style="width:100%; text-align:left">
<tr><td style="width:56px"><img src="../../artwork/clock48.png" width="48" height="48" border="0" alt="Clock" /></td>
<td style="font-size: 160%; font-weight: bold"><?php echo $string['activepaper']; ?></td></tr>
<tr><td></td><td><hr width="100%" style="border: 0px; color: #808080; background-color: #808080" noshade="noshade" size="1" /></td></tr>
<tr><td>&nbsp;</td><td><span class="date"><?php echo englishDate($_GET['start_date']); ?></span> <?php echo $string['to']; ?> <span class="date"><?php echo englishDate($_GET['end_date']); ?></span>.</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td><?php echo $string['msg']; ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td><strong><?php echo $string['solution']; ?></strong><br /><?php echo $string['solutionmsg']; ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" style="width: 120px" value="<?php echo $string['cancel']; ?>" onclick="window.close();" /></td></tr>
</table>
</form>

</body>
</html>