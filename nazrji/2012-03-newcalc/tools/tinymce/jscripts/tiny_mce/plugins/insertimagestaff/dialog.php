<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require  '../../../../../../include/staff_auth.inc';
  $path = $cfg_web_root . 'help/staff/images/';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Upload Image</title>
<style type="text/css"> 
body {background-color:#EEECDC; color:black; font-family:Arial,sans-serif; font-size:100%; margin:0px}
input, textarea {font-family:Arial,sans-serif}
</style>
<?php
if (isset($_FILES['FileName']) and $_FILES['FileName'] != '') {
    //proc upload
    
    $imageInfo = getimagesize($_FILES['FileName']['tmp_name']);
    $filename = $_FILES['FileName']['tmp_name'];
    
    //make the dirs
    if(!file_exists($path)) {
      mkdir($path, 0744);
    }
        
    //move orignal file 
    $imageInfo = getimagesize($_FILES['FileName']['tmp_name']); 
    $worked = move_uploaded_file($_FILES['FileName']['tmp_name'],$path . $_FILES['FileName']['name']);
    if (!$worked) {
      echo "Failed to copy file to: " . $path . $_FILES['FileName']['name'];
      exit;
    }
    $html = '<img width="' . $imageInfo[0] . '" height="' . $imageInfo[1] . '" alt="' . $_FILES['FileName']['name'] . '" src="./images/' . $_FILES['FileName']['name'] . '" border="0" />'; 
    
    ?>
        <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
        <script type="text/javascript" language="javascript">
tinyMCEPopup.requireLangPack();

var ExampleDialog = {
	init : function() {
	},

	insert : function() {
		// Insert the contents from the input into the document
        var html = '<?php echo $html; ?>';
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(ExampleDialog.init, ExampleDialog);
        </script>
        </head>
        <body onload="ExampleDialog.insert();">
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
    window.resizeTo(500,300);
    window.moveTo(winx,winy);
</script>
<form name="uploadImage" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING']; ?>">
<table border="0" cellpadding="4" cellspacing="0" width="100%" style="font-size:100%">
<tr><td style="background-color:white; width:56px"><img src="../../../../../../artwork/folder_image.png" width="48" height="48" border="0" alt="Image" /></td><td style="width:90%; background-color:white; text-align:left; font-size:140%; font-weight:bold">Add New Image</td></tr>
<tr><td colspan="2" style="background-color:#EEECDC">&nbsp;</td></tr>
<tr><td colspan="2" style="background-color:#EEECDC">Browse for the image file you wish to add (GIF, PNG or JPEG).</td></tr>
<tr><td colspan="2" style="background-color:#EEECDC" align="left">
  <div id="waitmsg" style="display:none; filter:progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=4); position:absolute; left:70px; top:25px; width:320px; height:190px; background-color: white; border: black 1px solid; color: black; font-size: 20pt; font-family: Arial,sans-serif; text-align:center"><br /><strong>Please Wait<br /></strong><br /><div style="font-size:10pt">This could take a few minutes<br />depending on network speed.</div><br /><div align="center"><img src="../artwork/green_progress_bar.gif" width="150" height="13" alt="Progress Bar" /></div></div>
    <input type="file" name="FileName" accept="image/gif,image/jpeg,image/pjpeg,image/png" size="50" /><br />
</td></tr>
<tr><td colspan="2" style="background-color:#EEECDC">&nbsp;</td></tr>
<tr><td colspan="2" style="background-color:#EEECDC" align="center"><input type="submit" name="submit" value="Insert" onclick="document.getElementById('waitmsg').style.display='block'" style="width:110px" />&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.close();" style="width:110px" /></td></tr>
</table>
</form>
 
<?php
}
?>
</body>
</html>
