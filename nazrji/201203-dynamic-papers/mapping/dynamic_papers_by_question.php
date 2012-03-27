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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/mapping.inc';
require '../include/errors.inc';
require '../include/dynamic_papers.inc.php';

check_var('module', 'REQUEST', true, false);

$module = $_REQUEST['module'];
$mode = 'question';

$years = get_years($module, $mysqli, 'all');
$session = (isset($_REQUEST['session'])) ? $_REQUEST['session'] : $years[0];

$temp_array = array();
$row_no = 0;

$result = $mysqli->prepare("SELECT q_group, q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited FROM questions q INNER JOIN relationships r ON q.q_id=r.question_id WHERE r.module_id=? AND r.paper_id IS NULL AND r.calendar_year=?");
$result->bind_param('ss', $module, $session);
$result->execute();
$result->bind_result($q_group, $q_id, $q_type, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited);
while ($result->fetch()) {
  $row_no++;
  $temp_array[$q_id]['q_type'] = $q_type;
  $temp_array[$q_id]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
  if (strlen($temp_array[$q_id]['leadin']) > 160) $temp_array[$q_id]['leadin'] = substr($temp_array[$q_id]['leadin'],0,160) . "...";
  $temp_array[$q_id]['q_id'] = $q_id;
  $temp_array[$q_id]['display_last_edited'] = $display_last_edited;
  $temp_array[$q_id]['q_media'] = $q_media;
  $temp_array[$q_id]['q_media_width'] = $q_media_width;
  $temp_array[$q_id]['q_media_height'] = $q_media_height;

  $temp_array[$q_id]['q_group'] = $q_group;
}
$result->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />

  <title>Rogō: <?php echo $string['dynamicpapers'] . ' - ' . $string['mappingbyquestion'] . ' ' . $cfg_install_type; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/dynamic_papers.css" />

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script src="../js/jquery.dynamicpapers.js" type="text/javascript"></script>
  <script src="../js/jquery.rquerystring.js" type="text/javascript"></script>
<?php
echo $cfg_js_root;
$langstrings = array('mustselectquestions', 'ajaxerror', 'ajaxconfirm');
echo LangUtils::render_JS_strings($langstrings, $string);
?>
</head>

<body>
<?php
  require '../include/dynamic_paper_options.inc.php';
?>

<div id="content" class="content">
  <table class="header">
    <tr>
      <th>
        <div class="breadcrumb">
          <a href="../staff/index.php"><?php echo $string['home'] ?></a>
          <?php
if ($module != '') {
?>
            <img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=<?php echo $module ?>"><?php echo $module ?></a>
<?php
}
?>
        </div>
        <div style="font-size:220%; font-weight:bold; margin-left:10px"><?php echo $string['dynamicpapers'] . ' - ' . $string['mappedquestions'] ?></div>
      </th>
      <th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th>
    </tr>
  </table>

  <table class="header">
    <tr>
      <th style="padding-top:1px">
        <table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; width:252px">
          <tr>
            <td class="tab"><a href="dynamic_papers_by_session.php?module=<?php echo $module; ?>"><?php echo $string['bysession']; ?></a></td>
            <td class="tab on"><?php echo $string['byquestion']; ?></td>
          </tr>
        </table>
      </th>
      <th style="width:100%; text-align:right"><?php echo render_year_dropdown($years, $module, $string, $session) ?></th>
    </tr>
    <tr><td colspan="2" style="background-color:#1E3C7B">&nbsp;</td></tr>
  </table>

<?php
  if (count($temp_array) > 0) {
?>
    <form action="./" method="post">
    <ul class="map-objectives questions">
<?php
  }

  foreach ($temp_array as $question) {
    $objByModule = getObjectivesByMapping($module, $session, null, $question['q_id'], $mysqli);

    if (count($objByModule) > 0 or $question['q_type'] == 'info') {
      $class = 'mapped';
    } else {
      $class = 'unmapped';
    }

    if ($question['leadin'] != '') {
      echo '<li class="' . $class . '"><input type="checkbox" id="qn-mapped' . $question['q_id'] . '" name="qn-mapped" value="' . $question['q_id'] . '" class="sel-question offscreen" /> <label for="qn-mapped' . $question['q_id'] . '" class="map-item map-question">' . $question['leadin'] . '</label>';

      //output mappings
      $sessiontitle = '';
      if (count($objByModule) > 0) {
        if (isset($objByModule['none_of_the_above']['mapped']) and $objByModule['none_of_the_above']['mapped'] == 1) {
          echo "<ul class=\"$class\">\n<li class=\"warning\"><strong>" . $string['warning'] . ":</strong> " . $string['questiononnotmap'] . "</li></ul>\n";
        } else {
          echo "<ul class=\"$class\">\n";
          foreach ($objByModule as $module => $mappings) {
            foreach ($mappings as $id => $mappingData) {
              if( $mappingData['session']['class_code'] != '') {
                $sessiondata = $mappingData['session']['class_code'];
                $sessiontitle = $mappingData['session']['title'];
                $sessiontitle .= ' ' . $mappingData['session']['occurrance'];
              } else {
                $sessiondata = $mappingData['session']['title'];
              }
              echo '<li>';
              if (count($objByModule) > 1) {
                echo "$module: ";
              }
              echo $mappingData['content'];
              echo " <a href=\"" . $mappingData['session']['source_url'] . "\" target=\"_blank\" class=\"mapping\" title=\"$sessiontitle\">" . $sessiondata ."</a> <a href=\"#\" rel=\"{$mappingData['id']}_{$question['q_id']}\" class=\"unmap\">Unmap</a>";
              echo '</li>';
            }
          }
        }
        echo "</ul>\n";
      }
      echo "</li>\n";
    }
  }
  $mysqli->close();
  if (count($temp_array) > 0) {
?>
    </ul>
    <input type="hidden" name="module" id="module" value="<?php echo $module ?>" />
    <input type="hidden" name="session" id="session" value="<?php echo $session ?>" />
  </form>
<?php
  }
?>
</div>
</body>
</html>
