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
* Displays tasks for the papers frame (papers_menu.php).
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

ob_start('ob_gzhandler');
require '../include/staff_auth.inc';
//require '../include/question_types.inc';
require '../include/errors.inc';
//require '../include/calculate_marks.inc';
require '../include/paper_functions.inc.php';
require '../classes/paper.class.php';

check_var('paperID', 'GET', true, false);
$paperID = $_GET['paperID'];
$paper_found = false;

try {
  $paper = new Paper($mysqli, $userID, $string, $paperID);
  $paper_found = true;
} catch (RecordNotFoundException $rex) {
  $paper_found = 'Not found';
} catch (DatabaseException $dex) {
  $paper_found = 'Error';
}

$module = (isset($_GET['module'])) ? $_GET['module'] : '';
$folder = (isset($_GET['folder'])) ? $_GET['folder'] : '';
$folder_name = '';
get_module_folder_details($module, $folder, $folder_name, $userroles, $teams, $paper->get_modules(), $mysqli);

$questions = $paper->get_question_breakdown();

?>
<!DOCTYPE html >
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō<?php echo ' ' . $rogo_version . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/screen.css" />
  <link rel="stylesheet" type="text/css" href="../css/metrics.css" />

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/rgraph/RGraph.common.core.js" ></script>
  <script type="text/javascript" src="../js/rgraph/RGraph.common.dynamic.js" ></script>
  <script type="text/javascript" src="../js/rgraph/RGraph.common.tooltips.js" ></script>
  <script type="text/javascript" src="../js/rgraph/RGraph.common.key.js" ></script>
  <script type="text/javascript" src="../js/rgraph/RGraph.bar.js" ></script></head>
  <script type="text/javascript" src="../js/rgraph/RGraph.hbar.js" ></script></head>
  <script type="text/javascript" src="../js/rgraph/RGraph.pie.js" ></script></head>
  <script type="text/javascript" src="../js/jquery.metrics.js" ></script></head>

<body>
<?php
if ($paper_found !== true) {
  if ($paper_found == 'Not found') {
    echo render_missing($string, $support_email, $string['papernotfound']);
  } else {
    // TODO: translate
    echo render_missing($string, $support_email, 'Error connecting to datebase.');
  }
} elseif ($paper->get_deleted() != '') {
  echo render_deleted($string, $paper_title, $paper_ownerID, $userID);
} else {
  // Main content
?>
  <div id="content">
    <div id="header">
      <a href="#" onclick="launchHelp(1); return false;" id="help_link"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help'] ?>" border="0" /></a>
      <div class="breadcrumb clearfix">
        <ol>
          <li class="alpha"><a href="../staff/index.php"><?php echo $string['home'] ?></a></li>

  <?php
          if ($module != '') {
            echo '<li class="omega"><a href="../folder/details.php?module=' . $module . '">' . $module . '</a></li>';
          }
          if ($folder != '') {
            echo '<li class="omega"><a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a></li>';
          }
  ?>
        </ol>
      </div>
      <h1><?php echo $module . '  &mdash; ' . $string['metrics'] ?></h1>
</div>

<?php
}
$owner = $paper->get_owner_details();
?>
      <div id="details">
        <h2>Summary</h2>
        <dl class="clearfix">
          <dt>Owner</dt>
          <dd><?php echo $owner['fullname'] ?> (<?php echo $owner['username'] . ', ' . $owner['email'] ?>)</dd>
          <dt>Type</dt>
          <dd><?php echo $paper->get_type() ?></dd>
          <dt>Timing</dt>
          <dd>
            <?php echo $paper->get_start_date('d F Y,  H:i') ?> to <?php echo $paper->get_end_date('d F Y,  H:i') ?>
<?php
if ($paper->get_duration() != '') {
  echo ', duration ' . $paper->get_duration() . ' minutes';
}
?>
          </dd>
          <dt>Marking</dt>
          <dd>Total marks: <?php echo $questions['marks_total'] ?></dd>
          <dd class="beta">Pass: <?php echo $paper->get_pass_mark() ?>%, distinction: <?php echo $paper->get_distinction_mark() ?>%</dd>
          <dt>Bidirectional</dt>
          <dd> <?php echo $paper->get_bidirectional() ?></dd>
          <dt>No. Questions</dt>
          <dd><?php echo $questions['total'] ?></dd>
          <dt>Reviews</dt>
          <dd>&nbsp;</dd>
        </dl>
<?php
$type_count = count($questions['type']);
if ($type_count > 0) {
  $width = ($type_count >= 9) ? 900 : 60 + ($type_count * 80);
?>
  <h2>Questions by type</h2>
  <?php
  $g_data = setup_graph_data($questions['type'], 'questions');
  ?>
  <canvas id="q_by_type" width="<?php echo $width ?>" height="450">[No canvas support]</canvas>

  <script type="text/javascript">
    $(function () {
      drawRgraph('bar', 'q_by_type', [<?php echo $g_data['data'] ?>], [<?php echo $g_data['labels'] ?>], [<?php echo $g_data['tooltips'] ?>], 45);
    });
  </script>

  <?php
}

$type_count = count($questions['type_marks']);
if (count($questions['type_marks']) > 0) {
  $width = ($type_count >= 9) ? 900 : 60 + ($type_count * 80);
?>
  <h2>Marks by question type</h2>
<?php
  $g_data = setup_graph_data($questions['type_marks'], 'marks');
?>
  <canvas id="m_by_type" width="<?php echo $width ?>" height="450">[No canvas support]</canvas>

  <script type="text/javascript">
    $(function () {
      drawRgraph('bar', 'm_by_type', [<?php echo $g_data['data'] ?>], [<?php echo $g_data['labels'] ?>], [<?php echo $g_data['tooltips'] ?>], 45);
    });
  </script>

<?php
}

$screen_count = count($questions['screen']);
if ($screen_count > 0) {
  $width = ($screen_count >= 9) ? 900 : 60 + ($screen_count * 80);
?>
  <h2>Questions by screen</h2>
  <?php
  $g_data = setup_graph_data($questions['screen'], 'questions', 'Screen');
  ?>
  <canvas id="q_by_screen" width="<?php echo $width ?>" height="350">[No canvas support]</canvas>

  <script type="text/javascript">
    $(function () {
      drawRgraph('bar', 'q_by_screen', [<?php echo $g_data['data'] ?>], [<?php echo $g_data['labels'] ?>], [<?php echo $g_data['tooltips'] ?>]);
    });
  </script>

  <?php
}

$screen_count = count($questions['screen_marks']);
if ($screen_count > 0) {
  $width = ($screen_count >= 9) ? 900 : 60 + ($screen_count * 80);
?>
  <h2>Marks by screen</h2>
<?php
  $g_data = setup_graph_data($questions['screen_marks'], 'marks', 'Screen');
?>
  <canvas id="m_by_screen" width="<?php echo $width ?>" height="350">[No canvas support]</canvas>

  <script type="text/javascript">
    $(function () {
      drawRgraph('bar', 'm_by_screen', [<?php echo $g_data['data'] ?>], [<?php echo $g_data['labels'] ?>], [<?php echo $g_data['tooltips'] ?>]);
    });
  </script>

<?php
}

$bloom_count = isset($questions['bloom']) ? count($questions['bloom']) : 0;
if ($bloom_count > 0) {
  $width = ($bloom_count >= 9) ? 900 : 60 + ($bloom_count * 80);
?>
  <h2>Questions by Bloom's Taxonomy</h2>
<?php
  $g_data = setup_graph_data($questions['bloom'], 'questions');
?>
  <canvas id="q_by_bloom" width="<?php echo $width ?>" height=350">[No canvas support]</canvas>

  <script type="text/javascript">
    $(function () {
      drawRgraph('bar', 'q_by_bloom', [<?php echo $g_data['data'] ?>], [<?php echo $g_data['labels'] ?>], [<?php echo $g_data['tooltips'] ?>]);
    });
  </script>

<?php
}

$bloom_count = isset($questions['bloom_marks']) ? count($questions['bloom_marks']) : 0;
if ($bloom_count > 0) {
  $width = ($bloom_count >= 9) ? 900 : 60 + ($bloom_count * 80);
?>
  <h2>Marks by Bloom's Taxonomy</h2>
<?php
  $g_data = setup_graph_data($questions['bloom_marks'], 'questions');
?>
  <canvas id="m_by_bloom" width="<?php echo $width ?>" height=350">[No canvas support]</canvas>

  <script type="text/javascript">
    $(function () {
      drawRgraph('bar', 'm_by_bloom', [<?php echo $g_data['data'] ?>], [<?php echo $g_data['labels'] ?>], [<?php echo $g_data['tooltips'] ?>]);
    });
  </script>

<?php
}
?>
    <h2>Marks by learning outcome</h2>

    <p>Graphs provided by <a href="http://www.rgraph.net/">RGraph</a></p>

  </div>

</body>
</html>
<?php
function render_missing($string, $support_email, $message) {
  $assistance = sprintf($string['furtherassistance'], $support_email, $support_email);

  $html = <<< HTML
    <div class="warning">
      <h1>{$message}</h1>
      <hr size="1" align="left" width="500" />
      <p>{$assistance}</p>
    </div>

HTML;

  return $html;
}

function render_deleted($string, $paper_title, $paper_ownerID, $userID) {
  $deleted_parts = explode('[deleted', $paper_title);
  $message1 = sprintf($string['deleted_msg1'], $deleted_parts[0]);

  if ($paper_ownerID == $userID) {
    $message2 = "<li>" . $string['deleted_msg2'] . "</li>\n";
  } else {
    $result = $mysqli->prepare("SELECT title, surname, email FROM users WHERE id=?");
    $result->bind_param('i', $paper_ownerID);
    $result->execute();
    $result->bind_result($tmp_title, $tmp_surname, $tmp_email);
    $result->fetch();
    $result->close();
    $message2 = "<li>" . sprintf($string['deleted_msg3'], $tmp_email, $tmp_title, $tmp_surname). "</li>\n";
  }


  $html = <<< HTML
?>
    <div class="warning">
<?php
      <h1>{$string['paperdeleted']}</h1>
      <hr size="1" align="left" width="500" />
      <p>{$message1}</p>
      <ul>
        {$message2}
      </ul>
    </div>

HTML;

  return $html;
}

function setup_graph_data($source, $label_postfix='', $label_prefix='') {
  $g_data = array('data' => '', 'labels' => '', 'tooltips' => '');

  $i = 1;
  foreach ($source as $index => $count) {
    $g_data['data'] .= $count;
    $g_data['labels'] .= "'$index'";
    $g_data['tooltips'] .= "'<strong>{$label_prefix} {$index}</strong><br />{$count} {$label_postfix}'";
    if ($i < count($source)) {
      $g_data['data'] .= ',';
      $g_data['labels'] .= ',';
      $g_data['tooltips'] .= ',';
    }
    $i++;
  }

  return $g_data;
}
?>