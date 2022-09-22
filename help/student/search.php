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

require '../../include/staff_student_auth.inc';

$id = null;
$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'student', $language, $mysqli);

function getPath($path, $pageID, $tmp_highlight)
{
    $parts = explode('/', $path);
    $path = '<a class="searchpath" href="index.php?id=1">Help</a>';
    for ($i = 0; $i < count($parts); $i++) {
        if ($i == (count($parts) - 1)) {
            $path .= " > <a class=\"searchpath\" href=\"index.php?id=$pageID&highlight=$tmp_highlight\">" . $parts[$i] . '</a>';
        } else {
            $path .= ' > <a class="searchpath" href="display_folder.php?title=' . $parts[$i] . '">' . $parts[$i] . '</a>';
        }
    }
    
    return $path;
}

function displayTitle($title)
{
    $parts = explode('/', $title);
    $end_no = count($parts) - 1;
    return $parts[$end_no];
}

function drawHeader($tmp_page_no)
{
    global $page_size, $total_hits, $hit_stop, $page_total, $string;
    
    $hit_start = (($page_size * $tmp_page_no) - $page_size) + 1;
    $hit_stop = $page_size * $tmp_page_no;
    if ($hit_stop > $total_hits) {
        $hit_stop = $total_hits;
    }

    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
    echo "<tr><td style=\"background-color:#295AAD; color:white; font-weight:bold\">&nbsp;&nbsp;Results $hit_start-$hit_stop of $total_hits</td><td style=\"background-color:#295AAD; color:white; text-align:right\">Pages:&nbsp;";
    for ($i = 1; $i <= $page_total; $i++) {
        if ($i == $tmp_page_no) {
            echo "&nbsp;[<strong>$i</strong>]&nbsp;";
        } else {
            echo "&nbsp;<a class=\"page changepage\" href=\"#\" data-show=\"$i\" data-hide=\"$page_total\">$i</a>&nbsp;";
        }
    }
    if ($tmp_page_no > 1) {
        echo '&nbsp;<img class="changepage" data-show="' . ($tmp_page_no - 1) . '" data-hide="' . $page_total . '" src="../previous_active.png" width="11" height="11" alt="' . $string['previous'] . '" />&nbsp;';
    } else {
        echo '&nbsp;<img src="../previous_inactive.png" width="11" height="11" alt="" border="0" />&nbsp;';
    }
    if ($tmp_page_no < $page_total) {
        echo '&nbsp;&nbsp;<a class="page changepage" href="#" data-show="' . ($tmp_page_no + 1) . '" data-hide="' . $page_total . '">' . $string['next'] . '</a>&nbsp;<img class="changepage" data-show="' . ($tmp_page_no + 1) . '" data-hide="' . $page_total . '" src="../next_active.png" width="11" height="11" alt="Next" border="0" />&nbsp;';
    } else {
        echo '&nbsp;&nbsp;<span class="grey">' . $string['next'] . '</span>&nbsp;<img src="../next_inactive.png" width="11" height="11" alt="" />&nbsp;';
    }
    echo "</td></tr></table>\n";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <title><?php echo page::title('ExamSys: ' . $string['help']); ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help_search.css" />

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
<?php
  echo '<div style="font-size:130%; font-weight:bold; margin-bottom:5px; color:#295AAD">' . sprintf($string['searchedfor'], $_GET['searchstring']) . "</div>\n<br />\n";
  
if (isset($_GET['searchstring'])) {
    $searchstring = $_GET['searchstring'];
    $search_results = $help_system->find($searchstring);
    
    $total_hits = count($search_results);
    $page_size = 25;

    if ($total_hits == 0) {
        echo '<p>' . sprintf($string['noresults'], $_GET['searchstring']) . "</p>\n";
        echo '<div><strong>' . $string['tips'] . "</strong></div>\n";
        echo "<ul style=\"\">\n<li>" . $string['tipsli'] . "</li>\n</ul>\n";
    } else {
        $page_no = 0;
        $link_no = 0;
        $page_total = ceil($total_hits / $page_size);
        $hit_start = (($page_size * $page_no) - $page_size) + 1;
        $hit_stop = $page_size * $page_no;
        foreach ($search_results as $search_result) {
            if ($link_no > 0) {
                echo '<tr><td class="row1"><img src="../single_page.png" class="icon16_active" /></td><td class="row2">';
            } else {
                // Start a new page.
                if ($hit_stop > $total_hits) {
                    $hit_stop = $total_hits;
                }
                if ($page_no > 0) {
                    echo "</table>\n";
                    drawHeader($page_no);
                    echo "</div>\n";
                }
                $page_no++;
                if ($page_no == 1) {
                    echo "<div id=\"page$page_no\"class=\"searchpage\" style=\"display:block\">\n";
                } else {
                    echo "<div id=\"page$page_no\" class=\"searchpage\" style=\"display:none\">\n";
                }
                drawHeader($page_no);
                echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
                echo '<tr><td style="padding:2px; vertical-align:top; width:24px"><img src="../single_page.png" class="icon16_active" /></td><td style="padding-bottom:10px">';
            }
            echo '<a class="title" href="index.php?id=' . $search_result['id'] . '&highlight=' . $searchstring . '">' . displayTitle($search_result['title']) . '</a><br /><div class="searchpath">' . getPath($search_result['title'], $search_result['id'], $searchstring) . "<div></td></tr>\n";
            $link_no++;
            if ($link_no >= $page_size) {
                $link_no = 0;
            }
        }
        echo "</table>\n";
        drawHeader($page_no);
        echo "</div>\n";
    }
}
  $mysqli->close();
?>
      </div>
  </div>
<?php
  // JS utils dataset.
  $render = new render($configObject);
  $jsdataset['name'] = 'jsutils';
  $jsdataset['attributes']['xls'] = json_encode($string);
  $render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
