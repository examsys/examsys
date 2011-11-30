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
* @author  Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
$path = $cfg_web_root . 'staff_help/images/';

function unique_filename($filename, $path) {
  if ($filename != '' and $filename != 'none') {
    $tmp_file_no = 0;
    $filename = str_replace('(','',$filename);
    $filename = str_replace(')','',$filename);
    $filename = str_replace('[','',$filename);
    $filename = str_replace(']','',$filename);
    $filename = str_replace('@','',$filename);
    $filename = str_replace('+','',$filename);
    while (file_exists($path . $filename)) {
      $tmp_file_ext = substr($filename,strpos($filename,".")+1);
      if (intval(substr($filename,strpos($filename,".")-4)) > 0) {
        $tmp_file_name = substr($filename,0,strpos($filename,".")-4);
        $tmp_file_no = substr($filename,strpos($filename,".")-4,4);
      } elseif (intval(substr($filename,strpos($filename,".")-3)) > 0) {
        $tmp_file_name = substr($filename,0,strpos($filename,".")-3);
        $tmp_file_no = substr($filename,strpos($filename,".")-3,3);
      } elseif (intval(substr($filename,strpos($filename,".")-2)) > 0) {
        $tmp_file_name = substr($filename,0,strpos($filename,".")-2);
        $tmp_file_no = substr($filename,strpos($filename,".")-2,2);
      } elseif (intval(substr($filename,strpos($filename,".")-1)) > 0) {
        $tmp_file_name = substr($filename,0,strpos($filename,".")-1);
        $tmp_file_no = substr($filename,strpos($filename,".")-1,1);
      } else {
        // Trap the filename with no number.
        $tmp_file_name = substr($filename,0,strpos($filename,"."));
      }
      $tmp_file_no++;
      $filename = $tmp_file_name . $tmp_file_no . "." . $tmp_file_ext;
    }
  } else {
    $filename = '';
  }
  return $filename;
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>New Captivate Tutorial</title>
<style type="text/css"> 
body {background-color:#EEECDC; color:black; font-family:Arial,sans-serif; font-size:90%; margin:0px}
input, textarea {font-family:Arial,sans-serif}
</style>
<?php
if ($_FILES['FileName'] != '') {
    //proc upload
    
    $filename = $_FILES['FileName']['tmp_name'];
    //make the dirs
    if(!file_exists($path)) {
        mkdir($path, 0744);
    }
    //move orignal file 
    $unique_name = unique_filename($_FILES['FileName']['name'], $path);
     
    $worked = move_uploaded_file($_FILES['FileName']['tmp_name'],$path . $unique_name);
    if (!$worked) {
      echo "Failed to copy file to: " . $path . $_FILES['FileName']['name'];
      exit;
    }
$html = '<div>
  <table style="CURSOR: pointer" onclick="openTutorial(\'' . $_FILES['FileName']['name'] . '\')" border="0" cellspacing="0" cellpadding="0">
   <tbody>
     <tr><td rowspan="2" style="width:70px"><img border="0" alt="Demo Movie" src="./large_play_icon.png" width="64" height="64" alt="play" /></td><td><div style="font-size:125%; color:blue">' . $_POST['title'] . '</div><div style="font-size:90%; color:#808080">Flash required</div></td></tr>
  </tbody>
  </table>
  </div>';
    
    ?>
        <script type="text/javascript" language="javascript">
        function retunHtmlToMainWindow() {
           var html = "<?php echo   str_replace('"','\"',str_replace("\n",'',$html)) . "\";\n" ; ?>
           
                var oEdit=window.opener.top.content.oUtil.obj;
                oEdit.insertHTML(html);
                self.close();
                 
        }
        </script>
        </head>
        <body onload="retunHtmlToMainWindow();" >
    <?php    
} else {
  //defaut state
  echo "<body>";   
  showForm('');
  exit;  
} 

function showForm($error) {
?>
<script type="text/javascript" language="javascript"> 
    var winx = (screen.width / 2) - 250;
    var winy = (screen.height / 2) - 150;
    window.resizeTo(500,500);
    window.moveTo(winx,winy);
</script>
<form name="uploadImage" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING']; ?>">
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size:100%">
<tr><td style="background-color:white; width:65px"><img src="large_captivate_icon.png" width="56" height="56" border="0" alt="Image" /></td><td style="width:85%; background-color:white; text-align:left; font-size:140%; font-weight:bold">&nbsp;New Captivate Tutorial</td></tr>
</table>

<table border="0" cellpadding="0" cellspacing="4" width="100%" style="font-size:100%">
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td></td><td>Browse for the flash file you wish to add (SWF).</td></tr>
<tr><td><strong>File</strong></td><td>
  <div id="waitmsg" style="display:none; filter:progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=4); position:absolute; left:70px; top:25px; width:320px; height:190px; background-color:white; border:black 1px solid; color:black; font-size:20pt; font-family:Arial,sans-serif; text-align:center"><br /><strong>Please Wait<br /></strong><br /><div style="font-size: 10pt">This could take a few minutes<br />depending on network speed.</div><br /><div align="center"><img src="../artwork/green_progress_bar.gif" width="150" height="13" alt="Progress Bar" /></div></div>
    <input type="file" name="FileName" size="45" /><br />
</td></tr>
<tr><td><strong>Title</strong></td><td><input name="title" type="text" value="" size="50" style="width:100%" /></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Insert" onclick="document.getElementById('waitmsg').style.display='block'" style="width:110px" />&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.close();" style="width:110px" /></td></tr>
</table>
</form>
<?php
}
?>
</body>
</html>