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
<html>
<head>
<title>Help and Support Center<?php echo " $cfg_install_type"; ?></title>
<style>
html {margin:0px; width:100%; height:100%; overflow:hidden}
body {margin:0px; width:100%; height:100%; overflow:hidden; font-size:75%; background-color:#F1F5FB; color:#154A93; font-family:Arial,sans-serif}
div {line-height:180%}
a:link.book {color:#154A93}
a:visited.book {color:#154A93}
a:link {color:#003DB2}
a:visited {color:#003DB2}
#main {height:100%; width:100%; overflow:scroll; border-left:2px solid #7699C7; border-right:2px solid #7699C7; border-bottom:2px solid #7699C7; padding:2px}
</style>

<script language="Javascript">
  function updateMenu(sectionID,imageID) {
    current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
    document.getElementById(sectionID).style.display = current;

    icon = (document.getElementById(imageID).getAttribute('src') == '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/help/open_book.png') ? '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/help/closed_book.png' : '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/help/open_book.png';
    document.getElementById(imageID).setAttribute('src',icon);
  }

  function resizeTOC() {
    if (parseInt(navigator.appVersion)>3) {
      if (navigator.appName=="Netscape") {
        winW = window.innerWidth;
        winW = winW - 8;
        document.getElementById("main").style.width = winW + 'px';
        
        winH = window.innerHeight;
        winH = winH - 6;
        document.getElementById("main").style.height = winH + 'px';
      }
    }
  }
  </script>
</head>
<body onload="resizeTOC()">

<div id="main">
<?php
  $sub_section = 0;
  $search_results = $mysqli->query("SELECT id, title FROM student_help WHERE id != 1 AND deleted IS NULL ORDER BY title, id");
  if (!$search_results) {
    echo $mysqli->error;
    exit;
  }
  $help_section = 0;
  $help_toc = array();
  while ($row = $search_results->fetch_assoc()) {
    $help_toc[$help_section]['id'] = $row['id'];
    $help_toc[$help_section]['title'] = $row['title'];
    $help_section++;
  }
  $search_results->close();

  $old_title = '';

  for ($i=0; $i<$help_section; $i++) {
    $slash_pos = strpos($help_toc[$i]['title'], '/');
    if ($slash_pos !== false) {
      $tmp_title = substr($help_toc[$i]['title'], ($slash_pos + 1));
      $icon = 'single_page.png';
    } else {
      if ($old_title != '' and strpos($help_toc[($i)]['title'], $old_title) === false and $sub_section == 1) {
        echo "</div>\n";
        $sub_section = 0;
      }
      $tmp_title = $help_toc[$i]['title'];
      if (isset($help_toc[($i+1)]['title']) and strpos($help_toc[($i+1)]['title'], $tmp_title . '/') !== false) {
        $icon = 'closed_book.png';
        for ($a=$i; $a<$help_section; $a++) {
          if (isset($_GET['id']) and $_GET['id'] == $help_toc[$a]['id']) $icon = 'open_book.png';
          if (isset($help_toc[($a+1)]['title']) and strpos($help_toc[($a+1)]['title'], $tmp_title . '/') === false) break;
        }
        $old_title = $tmp_title;
      } else {
        $icon = 'single_page.png';
        $old_title = $tmp_title;
      }
    }
    $id = $help_toc[$i]['id'];
    if ($icon == 'closed_book.png') {
      echo "<div><img src=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/help/$icon\" id=\"button$id\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" style=\"cursor:pointer\" onclick=\"updateMenu('submenu$id','button$id'); return false;\" />&nbsp;<a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
      echo "<div style=\"display:none; margin-left:18px\" id=\"submenu$id\">";
      $sub_section = 1;
    } elseif ($icon == 'open_book.png') {
      echo "<div><img src=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/help/$icon\" id=\"button$id\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" style=\"cursor:pointer\" onclick=\"updateMenu('submenu$id','button$id'); return false;\" />&nbsp;<a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
      echo "<div style=\"display:block; margin-left:18px\" id=\"submenu$id\">";
      $sub_section = 1;
    } else {
      if ($old_title != '' and strpos($help_toc[($i)]['title'], $old_title) === false and $sub_section == 1) {
        echo "</div>\n";
        $sub_section = 0;
      }
      echo "<div id=\"title$id\"><a href=\"display_page.php?id=$id\" target=\"content\"><img src=\"../$icon\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></a>&nbsp;<a href=\"display_page.php?id=$id\" target=\"content\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
    }

  }

  if ($sub_section == 1) echo "</div>\n";
  echo "<input type=\"hidden\" name=\"old_highlight\" value=\"0\" />";
  $mysqli->close();
?>
</div>
</body>
</html>