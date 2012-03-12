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
  
  if (isset($_POST['submit'])) {
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rename/Delete Keywords</title>
</head>
<?php
    // Perform renames first
    if ($_POST['renamelist'] != '') {
      $renamelist = str_replace('&quot;','"',$_POST['renamelist']);
      $to_rename = explode(';',$renamelist);
      foreach ($to_rename as $rename_word) {
        if ($rename_word != '') {
          $parts = explode('=',$rename_word);
          $keywordID = $parts[0];
          $new_keyword = str_replace('"','\"',$parts[1]);
          
          $keyword_details = $mysqli->prepare("SELECT id FROM keywords_user WHERE keyword=? AND userID=?");
          $keyword_details->bind_param('si', $new_keyword, $userID );
          $keyword_details->execute();
          $keyword_details->store_result();
          $keyword_details->bind_result($existing_id);
          if ($keyword_details->num_rows > 0) {   // New keyword already exists, just delete the old one.
            $result = $mysqli->prepare("DELETE FROM keywords_user WHERE id=? AND userID=?");
            $result->bind_param('ii', $keywordID, $userID);
            $result->execute();  
            $result->close();
            
            $result = $mysqli->prepare("UPDATE keywords_question SET keywordID=? WHERE keywordID=?");
            $result->bind_param('ii', $existing_id, $keywordID);
            $result->execute();  
            $result->close();
          } else {
            $result = $mysqli->prepare("UPDATE keywords_user SET keyword=? WHERE id=?");
            $result->bind_param('si', $new_keyword, $keywordID);
            $result->execute();  
            $result->close();
          }
          $keyword_details->close();
        }
      }
    }    

    // Perform deletes second
    if ($_POST['deletelist'] != '') {
      $to_delete = explode(';',$_POST['deletelist']);
      foreach ($to_delete as $deleteID) {
        // Delete the keyword from questions
        $result = $mysqli->prepare("DELETE FROM keywords_question WHERE keywordID=?");
        $result->bind_param('i', $deleteID);
        $result->execute();  
        $result->close();
       
        // Delete the keyword itself
        $result = $mysqli->prepare("DELETE FROM keywords_user WHERE id=?");
        $result->bind_param('i', $deleteID);
        $result->execute();  
        $result->close();
      }
    }
?>
<body onload="window.close();">
</body>
</html>
<?php
  } else {
?>
<html>
<head>
<title>Rename/Delete Keywords</title>
<style type="text/css">
  .indenton {text-indent:-23px; padding-left:23px; background-color:highlight; color:white}
  .indentoff {text-indent:-23px; padding-left:23px; background-color:white; color:black}
</style>
<script language="JavaScript">
  function removeOptionSelected() {
    var i;
    var list = '';
    for (i = 0; i<document.getElementById('keyword_no').value; i++) {
      if (document.getElementById('keyword' + i).checked) {
        document.getElementById('divkeyword' + i).style.display = 'none';
        window.opener.document.getElementById('keyword' + i).checked = false;
        window.opener.document.getElementById('divkeyword' + i).style.display = 'none';
        if (list == '') {
          list = document.getElementById('keyword' + i).value;
        } else {
          list += ';' + document.getElementById('keyword' + i).value;
        }
      }
    }
    document.getElementById('deletelist').value = list;
  }
  
  function renameOptionSelected() {
    var selVal = 0;
    var selIndex = 0;
    for (i = 0; i<document.getElementById('keyword_no').value; i++) {
      if (document.getElementById('keyword' + i).checked) {
        selVal = document.getElementById('keyword' + i).value;
        selIndex = i;
      }
    }
    
    renamewin=window.open("rename_keyword.php?keywordID=" +  selVal + "&index=" + selIndex + "","rkeywords","width=400,height=120,left="+(screen.width/2-200)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      renamewin.focus();
    }
  }
  
  function checkMultiple() {
    var i;
    var selectedNo = 0;
    for (i = 0; i<document.getElementById('keyword_no').value; i++) {
      if (document.getElementById('keyword' + i).checked) {
        selectedNo++;
      }
    }

    if (selectedNo == 0) {
      document.getElementById('delete').disabled = true;
      document.getElementById('rename').disabled = true;
    } else { 
      document.getElementById('delete').disabled = false;
      if (selectedNo > 1) {
        document.getElementById('rename').disabled = true;
      } else {
        document.getElementById('rename').disabled = false;
      }
    }
  }
  
  function toggle(objectID) {
    if (document.getElementById(objectID).className == 'indentoff') {
      document.getElementById(objectID).className = 'indenton';
    } else {
      document.getElementById(objectID).className = 'indentoff';
    }
    checkMultiple();
  }
</script>
</head>

<body onload="loadParentList();" style="background-color:#EEEEEE; color:black; font-family:Arial,sans-serif; margin:0px; font-size:90%">
<form name="myform" action="" method="post">
<table cellpadding="0" cellspacing="4" border="0" width="100%" style="font-size:100%">
<tr><td colspan="2"><strong>Keywords:</strong></td></tr>
<tr><td>
<?php
  echo "<div style=\"height:278px;width:370px;overflow-y:scroll;border:1px solid #808080;background-color:white;font-size:90%\">";
  $keyword_no = 0;
  $keyword_details = $mysqli->prepare("SELECT id, keyword FROM keywords_user WHERE userID=? ORDER BY keyword");
  $keyword_details->bind_param('i', $userID );
  $keyword_details->execute();
  $keyword_details->bind_result($id, $keyword);
  while ($keyword_details->fetch()) {
    echo "<div class=\"indentoff\" id=\"divkeyword$keyword_no\"><input type=\"checkbox\" onclick=\"toggle('divkeyword$keyword_no')\" id=\"keyword$keyword_no\" name=\"keyword$keyword_no\" value=\"$id\">&nbsp;<span id=\"keytext$keyword_no\">$keyword</span></div>\n";
    $keyword_no++;
  }
  $keyword_details->close();
  echo "<input type=\"hidden\" name=\"keyword_no\" id=\"keyword_no\" value=\"" . ($keyword_no - 1). "\" /></div>\n";
?>
</td>
<td style="vertical-align:top"><input type="button" name="delete" id="delete" value="Delete" style="width:95px" onclick="removeOptionSelected();" disabled /><br />
<br /><input type="button" name="rename" id="rename" value="Rename..." style="width:95px" onclick="renameOptionSelected();" disabled /></td></tr>
<tr><td colspan="2" style="height:6px"></td></tr>
<tr><td colspan="2" align="right"><input type="submit" name="submit" value="OK" style="width:75px" />&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" style="width:75px" onclick="window.close();" />&nbsp;</td></tr>
</table>
<textarea cols="40" rows="0" style="display:none" id="deletelist" name="deletelist"></textarea>
<textarea cols="40" rows="0" style="display:block" id="renamelist" name="renamelist"></textarea>
</form>

</body>
</html>
<?php
}
?>