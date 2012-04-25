<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  if (strpos($_SERVER['PHP_SELF'],'student_help') !== false) {
    $help_type = 'student';
  } else {
    $help_type = 'staff';
  }

  require '../include/staff_auth.inc';    // Only let staff create links.
  
  if (isset($_POST['submit'])) {
    $insertQuery = "INSERT INTO " . $help_type . "_help VALUES (NULL, \"" . $_POST['title'] . "\", '" . $_POST['pageid'] . "', NULL, 'pointer', NULL, NULL)";
    if (!$mysqli->query($insertQuery)) {
      echo "<p>" . $mysqli->error . "</p>\n";
      echo "<p>$insertQuery</p>\n";
      exit;
    }
    $page_id = $mysqli->insert_id;
    ?>
    <html>
    <head>
    <title></title>
    <script language="JavaScript">
      function reloadHelp() {
        window.top.location='/touchstone/<?php echo $help_type; ?>_help/index.php?id=<?php echo $page_id; ?>';
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
<style>
body {margin:0px; font-size:85%; background-color:white; color:black; font-family:Arial,sans-serif}
div {line-height:180%}
a:link {color:black}
a:visited {color:black}
</style>

<script language="Javascript">
  function updateMenu(sectionID,imageID) {
    current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
    document.getElementById(sectionID).style.display = current;

    icon = (document.getElementById(imageID).getAttribute('src') == '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/touchstone/staff_help/images/open_book.png') ? '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/touchstone/staff_help/images/closed_book.png' : '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/touchstone/staff_help/images/open_book.png';
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
<p style="margin-left:20px"><input type="text" style="font-family:Verdana,sans-serif; color:#7598C4; font-size:160%; border: 1px solid #C0C0C0; font-weight:bold" size="50" name="title" value="Page Title..." /></p>

<div id="toc" style="margin-left:20px; padding:2px; border:#C0C0C0 solid 1px; width:400px; height:500px; overflow-y:scroll">
<?php
  $sub_section = 0;
  $query_string = "SELECT id, title FROM staff_help WHERE id != 1 ORDER BY title, id";
  $search_results = $mysqli->query($query_string);
  if (!$search_results) {
    echo mysql_error();
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

  for ($i=0; $i<$help_section; $i++) {
    $slash_pos = strpos($help_toc[$i]['title'], '/');
    if ($slash_pos !== false) {
      $tmp_title = substr($help_toc[$i]['title'], ($slash_pos + 1));
      $icon = 'single_page.png';
    } else {
      if (strpos($help_toc[($i)]['title'], $old_title) === false and $sub_section == 1) { 
        echo "</div>\n";
        $sub_section = 0;
      }
      $tmp_title = $help_toc[$i]['title'];
      if (strpos($help_toc[($i+1)]['title'], $tmp_title . '/') !== false) {
        $icon = 'closed_book.png';
        for ($a=$i; $a<$help_section; $a++) {
          if ($_GET['id'] == $help_toc[$a]['id']) $icon = 'open_book.png';
          if (strpos($help_toc[($a+1)]['title'], $tmp_title . '/') === false) break;
        }
        $old_title = $tmp_title;
      } else {
        $icon = 'single_page.png';
        $old_title = $tmp_title;
      }
    }
    if ($icon == 'closed_book.png') {
      echo "<div><img src=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/staff_help/images/$icon\" id=\"button$i\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" />&nbsp;<a href=\"\" onclick=\"updateMenu('submenu$i','button$i'); return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
      echo "<div style=\"display:none; margin-left:18px\" id=\"submenu$i\">";
      $sub_section = 1;
    } elseif ($icon == 'open_book.png') {
      echo "<div><img src=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/staff_help/images/$icon\" id=\"button$i\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" />&nbsp;<a href=\"\" onclick=\"updateMenu('submenu$i','button$i'); return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
      echo "<div style=\"display:block; margin-left:18px\" id=\"submenu$i\">";
      $sub_section = 1;
    } else {
      if (strpos($help_toc[($i)]['title'], $old_title) === false and $sub_section == 1) {
        echo "</div>\n";
        $sub_section = 0;
      }
      echo "<div><input type=\"radio\" name=\"pageid\" value=\"" . $help_toc[$i]['id'] . "\"><img src=\"./images/$icon\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" />&nbsp;<a style=\"color:#003DB2\" href=\"\" onclick=\"return false;\"><nobr>" . $tmp_title . "</nobr></a></div>\n";
    }

  }

  if ($sub_section == 1) echo "</div>\n";
?>
</div>
<br />
<div align="center"><input style="font-family:Arial,sans-serif; width:120px" type="submit" name="submit" value="Create Link" />&nbsp;&nbsp;<input style="font-family:Arial,sans-serif; width:120px" type="button" name="cancel" value="Cancel" onclick="history.back();" /></div>
</form>
</body>
</html>
<?php
  }
  $mysqli->close();
?>