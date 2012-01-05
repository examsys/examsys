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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php echo $string['createnewpaper'] . $cfg_install_type; ?></title>

<style>
  body {font-family:Arial,sans-serif; color:black; background-color:#F1F5FB; margin:6px; font-size:90%}
  table {font-size:100%}
  textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
  .icon {color:#001687; padding-top:15px; padding-bottom:15px; padding-left:0px; padding-right:0px; vertical-align:top; width:98px; font-size:8pt}
</style>

<script language="JavaScript">
  function over(id) {
    if (id != document.getElementById('paper_type').value) {
      document.getElementById(id).style.backgroundImage = "url('../artwork/over.png')";
    }
  }
  
  function out(id) {
    if (id != document.getElementById('paper_type').value) {
      document.getElementById(id).style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
    }
  }
  
  function activate(id) {
    document.getElementById('formative').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
    document.getElementById('progress').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
    document.getElementById('summative').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
    document.getElementById('survey').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
    document.getElementById('osce').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
    document.getElementById('offline').style.backgroundImage = "url('../artwork/blank_tick_cross.gif')";
  
    document.getElementById(id).style.backgroundImage = "url('../artwork/on.png')";
    document.getElementById('paper_type').value = id;
  }
  
  function checkForm() {
    if (document.theform.paper_type.value == '') {
      alert("<?php echo $string['msg1']; ?>");
      return false;
    }
    if (document.theform.paper_name.value == '') {
      alert("<?php echo $string['msg2']; ?>");
      return false;
    }
  }
</script>
</head>

<body>
<form name="theform" action="new_paper2.php" method="post" onsubmit="return checkForm();">
<div style="text-align:center; border:solid 1px #7F9DB9; background-color:white">
<table cellpadding="0" cellspacing="0" border="0" style="background-color:white; color:#001687; width:100%">
<tr>
<td colspan="8" style="font-weight:bold; background-color:#DDE7EE; color:#001687; border-bottom:1px solid #C5C5C5">&nbsp;<?php echo $string['papertype']; ?></td>
</tr>
<tr>
<td class="icon" onclick="activate('formative')" onmouseover="over('formative')" onmouseout="out('formative')" id="formative"><img src="../artwork/formative.png" width="48" height="48" border="0" alt="Formative Self-Assessment" /><br /><?php echo $string['formative self-assessment']; ?></td>
<td class="icon" onclick="activate('progress')" onmouseover="over('progress')" onmouseout="out('progress')" id="progress"><img src="../artwork/progress.png" width="48" height="48" border="0" alt="Progress Test" /><br /><?php echo $string['progress test']; ?></td>
<td class="icon" onclick="activate('summative')" onmouseover="over('summative')" onmouseout="out('summative')" id="summative"><img src="../artwork/summative.png" width="48" height="48" border="0" alt="Summative Exam" /><br /><?php echo $string['summative exam']; ?></td>
<td class="icon" onclick="activate('survey')" onmouseover="over('survey')" onmouseout="out('survey')" id="survey"><img src="../artwork/survey.png" width="48" height="48" border="0" alt="Survey" /><br /><?php echo $string['survey']; ?></td>
<td class="icon" onclick="activate('osce')" onmouseover="over('osce')" onmouseout="out('osce')" id="osce"><img src="../artwork/osce.png" width="48" height="48" border="0" alt="OSCE" /><br /><?php echo $string['osce station']; ?></td>
<td class="icon" onclick="activate('offline')" onmouseover="over('offline')" onmouseout="out('offline')" id="offline"><img src="../artwork/offline.png" width="48" height="48" border="0" alt="Offline" /><br /><?php echo $string['offline paper']; ?></td>
<td class="icon" onclick="activate('peer_review')" onmouseover="over('peer_review')" onmouseout="out('peer_review')" id="peer_review"><img src="../artwork/peer_review.png" width="48" height="48" border="0" alt="Peer Review" /><br />Peer Review</td>
<td>&nbsp;</td>
</tr>
</table>
</div>
<br />
<span style="color:#001687"><?php echo $string['name']; ?><span> <input type="text" id="paper_name" name="paper_name" value="" style="width:650px" />
<input type="hidden" name="module" value="<?php if (isset($_GET['module'])) echo $_GET['module']; ?>" />
<input type="hidden" id="paper_type" name="paper_type" value="" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<br />
<br />
<div style="text-align:right"><input onclick="window.close();" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px" />&nbsp;<input type="submit" name="submit" value="<?php echo $string['next']; ?>" style="width:100px" /></div>
</form>
</body>
</html>
