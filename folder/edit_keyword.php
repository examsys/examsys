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
require '../include/errors.php';
$keywordID = param::required('keywordID', param::INT, param::FETCH_GET);
$module = param::optional('module', '', param::INT, param::FETCH_REQUEST);
$result = $mysqli->prepare('SELECT keyword FROM keywords_user WHERE id = ?');
$result->bind_param('i', $keywordID);
$result->execute();
$result->bind_result($keyword);
$result->fetch();
$result->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['editkeyword']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/keyword.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/keywordinit.min.js"></script>

</head>

<body>
<h1><?php echo $string['editkeyword']; ?></h1>
<form id="theform" name="theform" action="" method="post" autocomplete="off">
<div>
    <input type="text" style="width:99%" id="new_keyword" name="new_keyword" value="<?php echo htmlspecialchars($keyword); ?>" required autofocus />
    <input type="hidden" name="keywordID" value="<?php echo $keywordID; ?>" />
    <span id="duplicateerror"><?php echo $string['duplicate']; ?></span>
</div>
<div align="right">
    <input type="submit" name="submit" value="<?php echo $string['ok']; ?>" class="ok" />
    <input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" />
    <input type="hidden" id="module" name="module" value="<?php echo $module; ?>" />
</div>
</form>

</body>
</html>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
// Dataset.
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['posturl'] = 'do_edit_keyword.php';
$render->render($miscdataset, array(), 'dataset.html');
$mysqli->close();
