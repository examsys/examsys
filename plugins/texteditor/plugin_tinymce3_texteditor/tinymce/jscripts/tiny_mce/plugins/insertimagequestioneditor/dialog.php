<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../../../../../../../include/staff_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Add New Image</title>

  <link rel="stylesheet" type="text/css" href="css/body.css" />
  <link rel="stylesheet" type="text/css" href="css/dialog.css" />
  <style type="text/css">
    body {font-size:90%}
    .field {text-align:right}
    .note {font-size:90%; color:#808080}
  </style>
<?php
  if (isset($_FILES['FileName']) and $_FILES['FileName'] != '') {
    //proc upload
    $mediadirectory = rogo_directory::get_directory('media');
    // Make the directory.
    $mediadirectory->create();
    $filename = $_FILES['FileName']['tmp_name'];

    //move original file
    $imageInfo = getimagesize($_FILES['FileName']['tmp_name']);

    $destName  = media_handler::unique_filename( $_FILES['FileName']['name'] );
    $destPath  = $mediadirectory->fullpath($destName);

    $worked    = move_uploaded_file( $_FILES['FileName']['tmp_name'], $destPath );

    if (!$worked) {
      echo "Failed to copy " . $_FILES['FileName']['name'] . " to: " . $destPath;
      exit;
    }

    if (isset($_POST['border']) and $_POST['border'] == 1) {
      $class = 'class="image_brd"';
    } else {
      $class = 'class="image_no_brd"';
    }

    $html = '<img width="' . $imageInfo[0] . '" height="' . $imageInfo[1] . '" alt="' . $_POST['alt'] . '" src="' . $mediadirectory->url($destName) . '" ' . $class . ' />';

    ?>
        <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
        <script>
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

      <body onload="ExampleDialog.insert();" class="dialog_body">
    <?php
} else {
  //defaut state
  echo "<body class=\"dialog_body\">";
  showForm('');
  exit;
}

function showForm($error) {
?>
<script>
    var winx = (screen.width / 2) - 250;
    var winy = (screen.height / 2) - 150;
    window.resizeTo(500, 350);
    window.moveTo(winx, winy);
</script>
<form name="uploadImage" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING']; ?>">

<table border="0" cellpadding="4" cellspacing="0" width="100%" style="font-size:100%">
<tr><td class="dialog_header" style="width:56px; border-bottom: 1px solid #CCD9EA"><img src="img/folder_image.png" width="48" height="48" alt="Image" /></td><td class="dialog_header" style="border-bottom: 1px solid #CCD9EA">Add New Image</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td></td><td class="note">Browse for the image file you wish to add (GIF, PNG or JPEG).</td></tr>
<tr><td class="field">File</td><td>
  <div id="waitmsg" style="display:none; box-shadow:3px 3px 3px rgba(100, 100, 100, 0.50); position:absolute; left:70px; top:25px; width:320px; height:190px; background-color: white; border:1px solid #868686; color: black; font-size: 20pt; text-align:center"><br /><strong>Please Wait<br /></strong><br /><div style="font-size:10pt">This could take a few minutes<br />depending on network speed.</div><div align="center"><img src="img/green_progress_bar.gif" width="150" height="13" alt="Progress Bar" /></div></div>
    <input type="file" name="FileName" accept="image/gif,image/jpeg,image/pjpeg,image/png" size="50" /><br />
</td></tr>
<tr><td class="field">alt</td><td><input type="text" name="alt" value="" size="40" /></td></tr>
<tr><td class="field">border</td><td><input type="checkbox" name="border" value="1" /></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Insert" onclick="document.getElementById('waitmsg').style.display='block'" class="ok" /><input type="button" name="cancel" value="Cancel" onclick="window.close();" class="cancel" /></td></tr>
</table>
</form>

<?php
}
?>
</body>
</html>
