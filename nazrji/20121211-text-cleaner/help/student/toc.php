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

  require '../../include/staff_student_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Help and Support Center<?php echo " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    html {width:100%; height:100%; overflow:hidden}
    body {width:100%; height:100%; overflow:hidden; font-size:75%; background-color:#F1F5FB; color:#154A93}
    div {line-height:180%}
    a:link.book {color:#154A93}
    a:visited.book {color:#154A93}
    a:link {color:#003DB2}
    a:visited {color:#003DB2}
    #main {height:100%; width:100%; overflow:scroll; border-left:2px solid #7699C7; border-right:2px solid #7699C7; border-bottom:2px solid #7699C7; padding:2px}
  </style>

  <script type="text/javascript" src="../../js/help_toc.js"></script>
</head>
<body onload="resizeTOC()" onresize="resizeTOC()">

<div id="main">
<?php
  $sub_section = 0;
  $help_section = 0;
  $old_title = '';
  $parent = '';
  $old_parent = '';
  $help_toc = array();
  $help_toc_titles = array();

  $result = $mysqli->prepare("SELECT id, title FROM student_help WHERE id != 1 AND deleted IS NULL ORDER BY title, id");
  $result->execute();
  $result->bind_result($id, $title);
  while ($result->fetch()) {
    $help_toc[$help_section]['id'] = $id;
    $help_toc[$help_section]['title'] = $title;
    $help_toc_titles[$id] = $title;
    $help_section++;
  }
  $result->close();

  $expand_id = 0;
  if (isset($_GET['id'])) {
    $slash_pos = strpos($help_toc_titles[$_GET['id']], '/');
    if ($slash_pos !== false) {
      $target_parent = substr($help_toc_titles[$_GET['id']], 0, $slash_pos);


      for ($i=0; $i<$help_section; $i++) {
        if (strpos($help_toc[$i]['title'], $target_parent) === 0 and $expand_id == 0) {
          $expand_id = $help_toc[$i]['id'];
        }
      }
    }
  }

  for ($i=0; $i<$help_section; $i++) {
    $id = $help_toc[$i]['id'];
    $slash_pos = strpos($help_toc[$i]['title'], '/');
    if ($slash_pos !== false) {
      $parent = substr($help_toc[$i]['title'], 0, $slash_pos);
      if ($old_parent != '' and $parent != $old_parent) {
        echo "</div>\n";
      }
      $tmp_title = substr($help_toc[$i]['title'], ($slash_pos + 1));

      if ($parent != $old_parent) {
        if ($expand_id == $id) {
          $icon = 'open_book.png';
          echo "<div><nobr><img src=\"../$icon\" id=\"button$id\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" style=\"cursor:pointer\" onclick=\"updateMenu('submenu$id','button$id'); return false;\" />&nbsp;<a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\">" . $parent . "</a></nobr></div>\n";
          echo "<div style=\"display:block; margin-left:18px\" id=\"submenu$id\">";
        } else {
          $icon = 'closed_book.png';
          echo "<div><nobr><img src=\"../$icon\" id=\"button$id\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" style=\"cursor:pointer\" onclick=\"updateMenu('submenu$id','button$id'); return false;\" />&nbsp;<a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\">" . $parent . "</a></nobr></div>\n";
          echo "<div style=\"display:none; margin-left:18px\" id=\"submenu$id\">";
        }
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

    echo "<div id=\"title$id\"><nobr><a href=\"display_page.php?id=$id\" target=\"content\"><img src=\"../$icon\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></a>&nbsp;<a href=\"display_page.php?id=$id\" target=\"content\">$tmp_title</a></nobr></div>\n";
  }

  if ($old_parent != '') echo "</div>\n";
  echo "<input type=\"hidden\" id=\"old_highlight\" value=\"0\" />";
  $mysqli->close();
?>
</div>
</body>
</html>