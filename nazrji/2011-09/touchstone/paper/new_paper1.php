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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Create New Paper<?php echo " $cfg_install_type"; ?></title>

<style>
  body {font-family:Arial,sans-serif; color:black; background-color:#F1F5FB; margin:6px; font-size:90%}
  table {font-size:100%}
  textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
</style>

<script language="JavaScript">
  function over(id) {
    if (id != document.getElementById('paper_type').value) {
      document.getElementById(id).src='../artwork/' + id + '_over.png';
    }
  }
  
  function out(id) {
    if (id != document.getElementById('paper_type').value) {
      document.getElementById(id).src='../artwork/' + id + '_off.png';
    }
  }
  
  function activate(id) {
    document.getElementById('formative').src='../artwork/formative_off.png';
    document.getElementById('progress').src='../artwork/progress_off.png';
    document.getElementById('summative').src='../artwork/summative_off.png';
    document.getElementById('survey').src='../artwork/survey_off.png';
    document.getElementById('osce').src='../artwork/osce_off.png';
    document.getElementById('offline').src='../artwork/offline_off.png';
  
    document.getElementById(id).src='../artwork/' + id + '_on.png';
    document.getElementById('paper_type').value = id;
  }
  
  function checkForm() {
    if (document.theform.paper_type.value == '') {
      alert("Please select the type of paper you wish to create.");
      return false;
    }
    if (document.theform.paper_name.value == '') {
      alert("Please enter a unique name for the paper.");
      return false;
    }
    
    paperTitle = document.theform.paper_name.value;
    for (a=0; a<paperTitle.length; a++) {
      char = paperTitle.substr(a,1);
      if (char == '&' || char == '#' || char == '@' || char == '?' || char == '^' || char == '~') {
        alert('A paper name cannot contain any of the following characters:\r      &  #  @  ?  ^  ~');
        return false;
      }
    }
  }
</script>
</head>

<body>
<form name="theform" action="new_paper2.php" method="post" onsubmit="return checkForm();">
<div style="text-align:center; border:solid 1px #7F9DB9; background-color:white">
<table cellpadding="0" cellspacing="1" style="background-color:white; width:100%">
<tr>
<td colspan="6" style="font-size:120%; font-weight:bold; background-color:#DDE7EE; color:#001687; border-bottom:1px solid #C5C5C5">&nbsp;Paper Type</td>
</tr>
<tr>
<td onclick="activate('formative')" onmouseover="over('formative')" onmouseout="out('formative')"><img id="formative" src="../artwork/formative_off.png" width="98" height="104" border="0" alt="Formative Self-Assessment" /></td>
<td onclick="activate('progress')" onmouseover="over('progress')" onmouseout="out('progress')"><img id="progress" src="../artwork/progress_off.png" width="98" height="104" border="0" alt="Progress Test" /></td>
<td onclick="activate('summative')" onmouseover="over('summative')" onmouseout="out('summative')"><img id="summative" src="../artwork/summative_off.png" width="98" height="104" border="0" alt="Summative Exam" /></td>
<td onclick="activate('survey')" onmouseover="over('survey')" onmouseout="out('survey')"><img id="survey" src="../artwork/survey_off.png" width="98" height="104" border="0" alt="Survey" /></td>
<td onclick="activate('osce')" onmouseover="over('osce')" onmouseout="out('osce')"><img id="osce" src="../artwork/osce_off.png" width="98" height="104" border="0" alt="OSCE" /></td>
<td onclick="activate('offline')" onmouseover="over('offline')" onmouseout="out('offline')"><img id="offline" src="../artwork/offline_off.png" width="98" height="104" border="0" alt="Offline" /></td>
</tr>
</table>
</div>
<br />
<span style="font-weight:bold; color:#001687; font-size:120%">Name<span> <input type="text" id="paper_name" name="paper_name" value="" style="width:650px" />
<input type="hidden" name="module" value="<?php if (isset($_GET['module'])) echo $_GET['module']; ?>" />
<input type="hidden" id="paper_type" name="paper_type" value="" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<br />
<br />
<div style="text-align:right"><input onclick="window.close();" type="button" name="cancel" value="Cancel" style="width:100px" />&nbsp;<input type="submit" name="submit" value="Next &gt;" style="width:100px" /></div>
</form>
</body>
</html>
