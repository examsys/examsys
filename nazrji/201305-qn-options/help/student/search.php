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

  require '../../include/staff_student_auth.inc';
  
  function getPath($path, $pageID, $tmp_highlight) {
    $parts = explode('/',$path);
    $path = '<a class="path" href="display_page.php?id=1">Help</a>';
    for ($i=0; $i<count($parts); $i++) {
      if ($i == (count($parts)-1)) {
        $path .= " > <a class=\"path\" href=\"display_page.php?id=$pageID&highlight=$tmp_highlight\">" . $parts[$i] . "</a>";
      } else {
        $path .= " > <a class=\"path\" href=\"display_folder.php?title=" . $parts[$i] . "\">" . $parts[$i] . "</a>";
      }
    }
    
    return $path;
  }
  
  function displayTitle($title) {
    $parts = explode('/',$title);
    $end_no = count($parts) - 1;
    return $parts[$end_no];
  }
  
  function drawHeader($tmp_page_no) {
    global $page_size, $total_hits, $hit_stop, $page_total, $string;
    
    $hit_start = (($page_size * $tmp_page_no) - $page_size) + 1;
    $hit_stop = $page_size * $tmp_page_no;
    if ($hit_stop > $total_hits) $hit_stop = $total_hits;

    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
    echo "<tr><td style=\"border-top: 1px solid #6B82B2; border-bottom:1px solid #6B82B2; border-left:1px solid #6B82B2; background-image:url(../search_bar_background.png); background-repeat:repeat-x; color:white; font-weight:bold\">&nbsp;&nbsp;Results $hit_start-$hit_stop of $total_hits</td><td style=\"border-top: 1px solid #6B82B2; border-bottom: 1px solid #6B82B2; border-right: 1px solid #6B82B2; background-image:url(../search_bar_background.png); background-repeat:repeat-x; color:white; text-align:right\">Pages:&nbsp;";
    for ($i=1; $i<=$page_total; $i++) {
      if ($i == $tmp_page_no) {
        echo "&nbsp;[<strong>$i</strong>]&nbsp;";
      } else {
        echo "&nbsp;<a class=\"page\" href=\"#\" onclick=\"displayPage($i,$page_total); return false;\">$i</a>&nbsp;";
      }
    }
    if ($tmp_page_no > 1) {
      echo '&nbsp;<img onclick="displayPage(' . ($tmp_page_no-1) . ',' . $page_total . ')" src="../previous_active.png" width="11" height="11" alt="' . $string['previous'] . '" border="0" />&nbsp;';
    } else {
      echo '&nbsp;<img src="../previous_inactive.png" width="11" height="11" alt="" border="0" />&nbsp;';
    }
    if ($tmp_page_no < $page_total) {
      echo '&nbsp;&nbsp;<a class="page" href="" onclick="displayPage(' . ($tmp_page_no+1) . ',' . $page_total . '); return false;">' . $string['next'] . '</a>&nbsp;<img onclick="displayPage(' . ($tmp_page_no+1) . ',' . $page_total . ')" src="../next_active.png" width="11" height="11" alt="Next" border="0" />&nbsp;';
    } else {
      echo '&nbsp;&nbsp;' . $string['next'] . '&nbsp;<img src="../next_inactive.png" width="11" height="11" alt="" border="0" />&nbsp;';
    }
    echo "</td></tr></table>\n";
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rogo</title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help_search.css" />
  
  <script type="text/javascript">
    function displayPage(targetID, page_no) {
      for (page=1; page<=page_no; page++) {
        document.getElementById('page' + page).style.display='none';
      }
      document.getElementById('page' + targetID).style.display='block';
      window.scrollTo(0,0)
    }
  </script>
</head>
<body>

<?php
  echo "<div style=\"font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4\">" . sprintf($string['searchedfor'], $_GET['searchstring']) . "</div>\n<br />\n";
  
  if (isset($_GET['searchstring'])) {
    $search_results = $mysqli->prepare("SELECT id, title, MATCH (title, body_plain) AGAINST (?) AS relevance FROM student_help WHERE MATCH (title, body_plain) AGAINST (? IN BOOLEAN MODE) AND deleted IS NULL ORDER BY relevance DESC");
    $search_results->bind_param('ss', $_GET['searchstring'], $_GET['searchstring']);
    $search_results->execute();
    $search_results->store_result();
    $search_results->bind_result($id, $title, $score);
    $total_hits = $search_results->num_rows;
    $page_size = 25;
    if (!$userObject->has_role(array('SysAdmin', 'External'))) {   // Don't record SysAdmin searches.
      $result = $mysqli->prepare("INSERT INTO help_searches VALUES (NULL, 'student', ?, NOW(), ?, ?)");
      $result->bind_param('isi', $userObject->get_user_ID(), $_GET['searchstring'], $total_hits);
      $result->execute();  
      $result->close();
    }
    if ($search_results->num_rows == 0) {
      echo "<p>" . sprintf($string['noresults'], $_GET['searchstring']) . "</p>\n";
      echo "<div><strong>" . $string['tips'] . "</strong></div>\n";
      echo "<ul style=\"\">\n<li>" . $string['tipsli'] . "</li>\n</ul>\n";
    } else {
      $page_no = 0;
      $link_no = 0;
      $page_total = ceil($total_hits / $page_size);
      $hit_start = (($page_size * $page_no) - $page_size) + 1;
      $hit_stop = $page_size * $page_no;
      while ($search_results->fetch()) {
        if ($link_no > 0) {
          echo "<tr><td class=\"row1\"><img src=\"../single_page.png\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></td><td class=\"row2\">";
        } else {
          // Start a new page.
          if ($hit_stop > $total_hits) $hit_stop = $total_hits;
          if ($page_no > 0) {
            echo "</table>\n";
            drawHeader($page_no);
            echo "</div>\n";
          }
          $page_no++;
          if ($page_no == 1) {
            echo "<div id=\"page$page_no\" style=\"display:block\">\n";
          } else {
            echo "<div id=\"page$page_no\" style=\"display:none\">\n";
          }
          drawHeader($page_no);
          echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
          echo "<tr><td style=\"padding:2px; vertical-align:top; width:24px\"><img src=\"../single_page.png\" width=\"16\" height=\"16\" alt=\"\" /></td><td style=\"padding-bottom:10px\">";
        }
        echo "<a class=\"title\" href=\"index.php?id=$id&highlight=" . $_GET['searchstring'] . "\" target=\"_top\">" . displayTitle($title) . "</a><br /><div class=\"path\">" . getPath($title, $id, $_GET['searchstring']) . "<div></td></tr>\n";
        $link_no++;
        if ($link_no >= $page_size) {
          $link_no = 0;
        }
      }
      echo "</table>\n";
      drawHeader($page_no);
      echo "</div>\n";
    }
    $search_results->free_result();
    $search_results->close();
  }
  $mysqli->close();
?>
</body>
</html>