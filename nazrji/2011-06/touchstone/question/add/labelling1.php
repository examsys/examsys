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

require '../../include/staff_auth.inc';
?>
<html>
<head>
<title>New Labelling Question</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
</head>

<body>
<form name="add_form" method="post" action="labelling2.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>&scrOfY=<?php echo $_GET['scrOfY']; ?>" enctype="multipart/form-data">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Labelling)</span>
  </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" style="font-size:90%; background-color:#F0F0E8">
  <tr><td>
  <table cellpadding="0" cellspacing="0" border="0" style="width:126px; font-size:90%">
  <tr>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../../artwork/tab_on.gif)">Editor</td>
  </tr></table>
  </td>
  <td style="width:100%; background-color:#DFECFF; text-align:right"><strong>Created:</strong>&nbsp;<?php echo date('d/m/Y'); ?>&nbsp;&nbsp;&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" style="background-color:#1E3C7B">&nbsp;</td>
  </tr>
</table>

  <div align="center">
  <table cellpadding="0" cellspacing="0" border="0">
    <tr>
    <td colspan="2" align="center">
    <table cellpadding="3" cellspacing="0" border="0">
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2">Please select the image file you would like to use as the basis for this new question.<br />Images must be in JPEG, GIF or PNG format and be no larger than 900x800 pixels.</td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td class="field">Image</td>
      <td><input type="file" size="55" name="qmedia" /></td>
    </tr>
    <tr>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:center"><input type="button" name="cancel" value="Cancel" style="width: 100px" onclick="javascript: history.back();" />&nbsp;<input type="submit" style="width: 100px" name="submit1" value="Next &gt;" /></td>
    </tr>
  </table>
  </div>
</form>

</body>
</html>
