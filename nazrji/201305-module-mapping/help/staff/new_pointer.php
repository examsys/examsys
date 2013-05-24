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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../../include/sysadmin_auth.inc';    // Only let SysAdmin staff create links.

if (isset($_POST['submit'])) {
  $insertQuery = "INSERT INTO staff_help VALUES (NULL, \"" . $_POST['title'] . "\", '" . $_POST['pageid'] . "', NULL, 'pointer', NULL, NULL, 'Staff', NULL)";
  if (!$mysqli->query($insertQuery)) {
    echo "<p>" . $mysqli->error . "</p>\n";
    echo "<p>$insertQuery</p>\n";
    exit;
  }
  $page_id = $mysqli->insert_id;
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Help and Support Center</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <script type="text/javascript">
    function reloadHelp() {
      window.top.location='index.php?id=<?php echo $page_id; ?>';
    }
  </script>
</head>
<body onload="reloadHelp()">
</body>
</html>

<?php
} else {
?>
<html>
<head>
  <title>Help and Support Center</title>
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
  body {font-size:85%}
  div {line-height:180%}
  a:link {color:black}
  a:visited {color:black}
  </style>

  <script language="Javascript">
    function updateMenu(sectionID,imageID) {
      current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
      document.getElementById(sectionID).style.display = current;

      icon = (document.getElementById(imageID).getAttribute('src') == '../open_book.png') ? '../closed_book.png' : '../open_book.png';
      document.getElementById(imageID).setAttribute('src',icon);
    }
    function resizeTOC() {
      var frHeight = parent.document.getElementById("content").height;
      frHeight = frHeight - 120;
      document.getElementById("toc").style.height = frHeight + 'px';
    }
  </script>
</head>
<body onload="resizeTOC()">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<p style="margin-left:20px"><input type="text" style="color:#7598C4; font-size:160%; border: 1px solid #C0C0C0; font-weight:bold" size="50" name="title" value="Page Title..." /></p>

<div id="toc" style="margin-left:20px; padding:2px; border:#C0C0C0 solid 1px; width:400px; height:500px; overflow-y:scroll">
<?php
  $sub_section = 0;
  $help_section = 0;
  $help_toc = array();
  
  $result = $mysqli->prepare("SELECT id, title FROM staff_help WHERE id != 1 ORDER BY title, id");
  $result->execute();
  $result->bind_result($page_id, $page_title);
  while ($result->fetch()) {
    $help_toc[$help_section]['id'] = $page_id;
    $help_toc[$help_section]['title'] = $page_title;
    $help_section++;
  }
  $result->close();

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
      if (strpos($help_toc[($i+1)]['title'], $tmp_title . '/') !== false) {
        $icon = 'closed_book.png';
        for ($a=$i; $a<$help_section; $a++) {
          if ($_GET['id'] == $help_toc[$a]['id']) $icon = '../open_book.png';
          if (strpos($help_toc[($a+1)]['title'], $tmp_title . '/') === false) break;
        }
        $old_title = $tmp_title;
      } else {
        $icon = 'single_page.png';
        $old_title = $tmp_title;
      }
    }
    if ($icon == 'closed_book.png') {
      echo "<div><img src=\"../$icon\" id=\"button$i\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" />&nbsp;<a href=\"\" onclick=\"updateMenu('submenu$i','button$i'); return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
      echo "<div style=\"display:none; margin-left:18px\" id=\"submenu$i\">";
      $sub_section = 1;
    } elseif ($icon == 'open_book.png') {
      echo "<div><img src=\"../$icon\" id=\"button$i\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" />&nbsp;<a href=\"\" onclick=\"updateMenu('submenu$i','button$i'); return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
      echo "<div style=\"display:block; margin-left:18px\" id=\"submenu$i\">";
      $sub_section = 1;
    } else {
      if (strpos($help_toc[($i)]['title'], $old_title) === false and $sub_section == 1) {
        echo "</div>\n";
        $sub_section = 0;
      }
      echo "<div><input type=\"radio\" name=\"pageid\" value=\"" . $help_toc[$i]['id'] . "\"><img src=\"../$icon\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" />&nbsp;<a style=\"color:#003DB2\" href=\"\" onclick=\"return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
    }

  }

  if ($sub_section == 1) echo "</div>\n";
?>
</div>
<br />
<div align="center"><input style="width:120px" type="submit" name="submit" value="Create Link" />&nbsp;&nbsp;<input style="width:120px" type="button" name="cancel" value="Cancel" onclick="history.back();" /></div>
</form>
</body>
</html>
<?php
  }
  $mysqli->close();
?>