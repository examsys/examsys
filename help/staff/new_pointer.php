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

require '../../include/sysadmin_auth.inc';    // Only let SysAdmin staff create links.

$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $pageID = $_POST['pageid'];

    $articleid = $help_system->create_pointer($title, $pageID);

    $mysqli->close();
    header("location: index.php?id=$articleid");
    exit;
} else {
    $id = null;
    ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title><?php echo page::title('ExamSys: ' . $string['help']); ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help.css" />
  <link rel="stylesheet" type="text/css" href="../../css/key.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../../js/require.js'></script>
  <script src='../../js/main.min.js'></script>
  <script type="text/javascript" src="../../js/helpinit.min.js"></script>
</head>
<body>
<div id="wrapper">
  <div id="toolbar">
    <?php $help_system->display_toolbar($id); ?>
  </div>

  <div id="toc">
    <?php $help_system->display_toc($id); ?>
  </div>
  <div id="contents">

    <p style="margin-left:20px" class="key"><?php echo $string['msg'] ?></p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
<p style="margin-left:20px"><input type="text" style="color:#295AAD; font-size:160%; border: 1px solid #C0C0C0; font-weight:bold" size="50" name="title" value="" placeholder="Page Title..." required /></p>

<div id="pointertoc" style="margin-left:20px; padding:2px; border:#C0C0C0 solid 1px; width:400px; height:500px; overflow-y:scroll">
    <?php
    if ($userObject->has_role('SysAdmin')) {
        $sql = 'SELECT articleid, title FROM staff_help WHERE roles IN ("SysAdmin", "Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
    } elseif ($userObject->has_role('Admin')) {
        $sql = 'SELECT articleid, title FROM staff_help WHERE roles IN ("Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
    } else {
        $sql = 'SELECT articleid, title FROM staff_help WHERE roles = "Staff" AND deleted IS NULL AND language = ? ORDER BY title, id';
    }

    $sub_section = 0;
    $old_title = '';
    $parent = '';
    $old_parent = '';
    $help_toc = array();
    $help_toc_titles = array();

    $help_section = 0;
    $result = $mysqli->prepare($sql);
    $result->bind_param('s', $language);
    $result->execute();
    $result->bind_result($id, $title);
    while ($result->fetch()) {
        $help_toc[$help_section]['id'] = $id;
        $help_toc[$help_section]['title'] = $title;
        $help_toc_titles[$id] = $title;
        $help_section++;
    }
    $result->close();

    for ($i = 0; $i < $help_section; $i++) {
        $id = $help_toc[$i]['id'];
        $slash_pos = mb_strpos($help_toc[$i]['title'], '/');
        if ($slash_pos !== false) {
            $parent = mb_substr($help_toc[$i]['title'], 0, $slash_pos);
            if ($old_parent != '' and $parent != $old_parent) {
                echo "</div>\n";
            }
            $tmp_title = mb_substr($help_toc[$i]['title'], ($slash_pos + 1));

            if ($parent != $old_parent) {
                $icon = 'closed_book.png';
                echo "<div class=\"pointer_book\" id=\"pointer_sect$id\"><img src=\"../$icon\" id=\"pointer_button$id\" class=\"icon16_active\" />" . $parent . "</div>\n";
                echo "<div class=\"pointer_closed_submenu\" id=\"pointer_submenu$id\">";
            }
            $old_parent = $parent;
            $icon = 'single_page.png';
        } else {
            if ($old_parent != '') {
                echo "</div>\n";
            }
            $tmp_title = $help_toc[$i]['title'];
            $icon = 'single_page.png';
            $parent = '';
            $old_parent = $parent;
        }
        echo "<div id=\"title$id\" class=\"pointer_page\"><input type=\"radio\" name=\"pageid\" value=\"$id\" id=\"radio$id\" /><label for=\"radio$id\"><img src=\"../$icon\" class=\"icon16_active\" />$tmp_title</label></div>\n";
    }

    if ($old_parent != '') {
        echo "</div>\n";
    }
    ?>
</div>
<br />
<div align="center"><input class="ok" type="submit" name="submit" value="<?php echo $string['createlink'] ?>" /><input data-help="staffpointer" class="cancel" type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="history.back();" /></div>
</form>
  </div>
</div>
</body>
</html>
    <?php
}
  $mysqli->close();
?>
