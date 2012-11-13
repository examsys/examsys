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
  require '../include/errors.inc';
  require '../classes/questioninfo.class.php';
  
  check_var('q_id', 'GET', true, false);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Information<?php echo ' ' . $cfg_install_type; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:80%}
    th {background-color:#CFDBEB; text-align:left; font-weight:normal}
    td {vertical-align:top}
    .screen {font-size:90%; color:#808080}
    .num {text-align:right; padding-right:6px}
  </style>
  
  <script type="text/javascript">
    function loadPaper(paperID) {
      window.opener.location = "../paper/details.php?paperID=" + paperID;
      window.close();
    }
    
    function loadModule(moduleID) {
      window.opener.location = "../folder/details.php?module=" + moduleID;
      window.close();
    }
    
    function openLongitudinal(questionID) {
      window.open("longitudinal_performance.php?q_id=" + questionID);
    }
  </script>
</head>

<body>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
<tr>
<td colspan="2" valign="middle" style="background-color:white; text-align:left; border-bottom:1px solid #CCD9EA">

<img src="../artwork/lrg_info_icon.png" width="37" height="37" alt="Information" style="float:left" /><span class="midblue_header" style="font-size:18pt; font-weight:bold">&nbsp;&nbsp;<?php echo $string['questioninformation']; ?></span>

</td>
</tr>
<?php
  echo question_info::full_question_information($_GET['q_id'], $mysqli,$userObject);
?>
</table>
</div>

<div style="text-align:center; padding-top:5px">
<form>
<input type="button" style="width: 120px" name="ok" onclick="javascript:window.close();" value="<?php echo $string['close']; ?>" />
</form>
</div>
</body>
</html>
