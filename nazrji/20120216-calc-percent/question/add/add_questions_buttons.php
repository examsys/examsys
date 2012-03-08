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
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Buttons</title>

  <script language="JavaScript">
    var selectedButton = 'unused';
  
    function buttonclick(sectionID, scriptName) {
      parent.qlist.iframeurl.location = scriptName;
      parent.qlist.previewurl.location = 'preview_default.php'
      
      document.getElementById('button_unused').style.background='';
      document.getElementById('button_alphabetic').style.background='';
      document.getElementById('button_keywords').style.background='';
      document.getElementById('button_status').style.background='';
      document.getElementById('button_papers').style.background='';
      document.getElementById('button_team').style.background='';
      document.getElementById('button_search').style.background='';

      document.getElementById('button_'+sectionID).style.background='url(../../artwork/2007_button_on.png)';
      selectedButton = sectionID;
    }

    function buttonover(buttonID) {
      if (buttonID != selectedButton) {
        document.getElementById('button_'+buttonID).style.backgroundImage='url(../../artwork/2007_button_over.png)';
      }
    }

    function buttonout(buttonID) {
      if (buttonID != selectedButton) {
        document.getElementById('button_'+buttonID).style.backgroundImage='';
      }
    }
  </script>
  
  <style type="text/css">
  body {font-size:100%; font-family:Arial,sans-serif; background-color:#DFECFF; color:black; margin-top:4px; margin-bottom:2px; margin-left:4px; margin-right:4px}
  </style>
</head>
<body>

<table cellspacing="0" cellpadding="2" style="font-size:90%; width:126px; height:99%; background-color:white; border:1px solid #95AEC8">
<tr><td style="vertical-align:top; text-align:center" valign="top">

<table cellspacing="0" cellpadding="2" style="font-size:90%; width:120px; background-white">
<tr><td id="button_unused" style="background-image:url('../../artwork/2007_button_on.png'); height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('unused')" onmouseout="buttonout('unused')" onclick="buttonclick('unused','add_questions_list_unused.php')">&nbsp;<?php echo $string['myunused']; ?></td></tr>
<tr><td id="button_alphabetic" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('alphabetic')" onmouseout="buttonout('alphabetic')" onclick="buttonclick('alphabetic','add_questions_list_all.php')">&nbsp;<?php echo $string['allmyquestions']; ?></td></tr>
<tr><td id="button_keywords" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('keywords')" onmouseout="buttonout('keywords')" onclick="buttonclick('keywords','add_questions_keywords_frame.php')">&nbsp;<?php echo $string['bykeywords']; ?></td></tr>
<tr><td id="button_status" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('status')" onmouseout="buttonout('status')" onclick="buttonclick('status','add_questions_by_status.php')">&nbsp;<?php echo $string['bystatus']; ?></td></tr>
<tr><td id="button_papers" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('papers')" onmouseout="buttonout('papers')" onclick="buttonclick('papers','add_questions_paper_types.php')">&nbsp;<?php echo $string['bypaper']; ?></td></tr>
<tr><td id="button_team" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('team')" onmouseout="buttonout('team')" onclick="buttonclick('team','add_questions_team_list.php')">&nbsp;<?php echo $string['byteam']; ?></td></tr>
<tr><td id="button_search" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('search')" onmouseout="buttonout('search')" onclick="buttonclick('search','add_questions_list_search.php')">&nbsp;<?php echo $string['search']; ?></td></tr>
</table>

</td></tr>
</table>

</body>
</html>
