<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['addfaculty']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; margin:2px; background-color:#EAEAEA}
    h1 {font-size:140%; font-weight:normal}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/addfacultyinit.min.js"></script>
</head>

<body>
<h1><?php echo $string['addfaculty'] ?></h1>
<form id="theform" name="myform" action="" method="post" autocomplete="off">
<table cellpadding="0" cellspacing="2" border="0">
<?php
echo '<tr><td class="field">' . $string['name'] . '</td><td><input type="text" style="width:99%" id="add_faculty" name="add_faculty" maxlength="80" value="" required autofocus /></td></tr>';
echo '<tr><td class="field">' . $string['code'] . '</td><td><input type="text" size="30" maxlength="30" id="code" name="code" value=""/></td></tr>';
$external = new \external_systems();
$extsys = $external->get_all_externalsystems();
?>
<tr><td class="field"><?php echo $string['externalsys'] ?></td><td><select name="externalsys">
<?php
  echo "<option value=\"\"></option>\n";
foreach ($extsys as $i => $s) {
    if (isset($externalsys) and $s == $externalsys) {
        $selected = 'selected';
    } else {
        $selected = '';
    }
    echo "<option value=\"$s\" $selected>$s</option>\n";
}
?>
</select></td></tr>
<tr><td class="field"><?php echo $string['externalid'] ?></td><td><input type="text" size="30" maxlength="255" name="externalid" value=""></td></tr>
</table>
<div align="right"><input type="submit" name="ok" value="<?php echo $string['ok'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" style="margin-right:0" /><input type="hidden" name="module" value="<?php if (isset($_GET['module'])) {
    echo $_GET['module'];
                                                         } ?>" /></div>
</form>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
