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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require_once '../../include/errors.php';

$paperid = check_var('paperID', 'GET', true, false, true);

function parse_leadin($id, $question, $type, $question_screen, $settings, $cur_screen) {
	if ($type == 'calculation') {
		$question = render_legacy_calc($id, $question, $question_screen, $cur_screen);
	} elseif ($type == 'enhancedcalc') {
		$question = render_calc($id, $question, $question_screen, $settings, $cur_screen);
	} else {
		$question = '<span style="color:#C0C0C0">' . $question . '</span>';
	}

	return $question;
}

function render_legacy_calc($id, $question, $question_screen, $cur_screen) {
	$variables = array ('A','B','C','D','E','F','G','H');

	foreach ($variables as $variable) {
		if ($question_screen < $cur_screen) {
			$question = str_replace('$' . $variable, '<input type="radio" name="ref" value="var' . $variable . $id . '">&nbsp;$' . $variable, $question);
		} else {
			$question = str_replace('$' . $variable, '<input type="radio" name="ref" value="var' . $variable . $id . '" disabled>&nbsp;$' . $variable, $question);
		}
	}
	if ($question_screen < $cur_screen) {
		$question .= ' <input type="radio" name="ref" value="ans' . $id . '"><input type="text" name="answer' . $id . '" size="14" value="student answer" />';
	} else {
		$question .= ' <input type="radio" name="ref" value="ans' . $id . '" disabled><input type="text" name="answer' . $id . '" size="14" value="student answer" disabled />';
	}

	return $question;
}

function render_calc($id, $question, $question_screen, $settings, $cur_screen) {
	$setting_arr = json_decode($settings, true);

	$variables = array_keys($setting_arr['vars']);

	$disabled = ($question_screen < $cur_screen) ? '' : ' disabled';
	foreach ($variables as $variable) {
		$question = str_replace($variable, '<input type="radio" name="ref" value="var' . $variable . $id . '"{$disabled}>&nbsp;' . $variable, $question);
	}
	$question .= ' <input type="radio" name="ref" value="ans' . $id . '"><input type="text" name="answer' . $id . '" size="14" value="student answer"{$disabled} />';

	return $question;
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	
  <title><?php echo $string['variablelink'] . ' ' . $configObject->get('cfg_install_type') ?></title>
	
  <link rel="stylesheet" href="../../css/body.css" type="text/css" />
  <link rel="stylesheet" href="../../css/finish.css" type="text/css" />
	<style type="text/css">
		body {font-size:90%; margin-bottom:5px}
		.title {background-color:#5590CF; color:white; font-size:160%; font-weight:bold; padding:6px}
		.q_no {text-align:right; vertical-align:top; cursor:pointer; width:50px; padding-right:6px; padding-left:6px}
		.divider {font-size:80%; font-weight:bold; padding-left:6px}
    .ok {font-size:90%}
  </style>

  <script>
    function copyValue() {
      for (var i=0; i < document.myform.ref.length; i++) {
        if (document.myform.ref[i].checked) {
          var selectedRef = document.myform.ref[i].value;
        }
      }

      window.opener.document.getElementById('<?php echo $_GET['elementID'] ?>').value = selectedRef;
      window.opener.document.getElementById('<?php echo $_GET['iconID'] ?>').src = '../../artwork/variable_link_on.png';
      window.close();
    }
  </script>
</head>

<body>
<form name="myform" action="" method="post" autocomplete="off">
<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; width:100%">
<tr>
<td colspan="2" class="title"><?php echo $string['variablelink']; ?></td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<?php
  $question_no = 1;
  $old_screen = 0;
  $previous_question = true;

  $paper_details = array();
  $q_no = 0;

  $result = $mysqli->prepare("SELECT q_id, leadin, q_type, settings, screen FROM papers, questions WHERE paper = ? AND papers.question = questions.q_id ORDER BY screen, display_pos");
  $result->bind_param('i', $paperid);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $leadin, $q_type, $settings, $screen);
  while ($result->fetch()) {
    $paper_details[$q_no]['q_id'] = $q_id;
    $paper_details[$q_no]['leadin'] = $leadin;
    $paper_details[$q_no]['q_type'] = $q_type;
    $paper_details[$q_no]['screen'] = $screen;
    $paper_details[$q_no]['settings'] = $settings;
    $q_no++;
  }
  $result->close();

  $current_screen = 0;
  if (isset($_GET['q_id']) and $_GET['q_id'] != -1) {
    for ($i=0; $i<$q_no; $i++) {
      if ($paper_details[$i]['q_id'] == $_GET['q_id']) $current_screen = $paper_details[$i]['screen'];
    }
  } else {
    $result = $mysqli->prepare("SELECT MAX(screen) FROM papers WHERE paper = ?");
    $result->bind_param('i', $paperid);
    $result->execute();
    $result->store_result();
    $result->bind_result($max_screen);
    $result->fetch();
    $result->close();
    if ($max_screen > 1) $current_screen = $max_screen;
  }

  for ($i=0; $i<$q_no; $i++) {
    if ($paper_details[$i]['screen'] > 1 and $old_screen != $paper_details[$i]['screen']) {
      echo '<tr><td colspan="2">&nbsp;</td></tr>';
      echo '<tr><td class="screenbrk" colspan="2"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $paper_details[$i]['screen'] . '</span></td></tr>';
		}
    echo "<tr><td class=\"q_no\">$question_no.</td><td>" . parse_leadin($paper_details[$i]['q_id'], $paper_details[$i]['leadin'], $paper_details[$i]['q_type'], $paper_details[$i]['screen'], $paper_details[$i]['settings'], $current_screen) . "</td></tr>\n";
    if ($paper_details[$i]['q_type'] != 'info') $question_no++;
    $old_screen = $paper_details[$i]['screen'];
  }

  $mysqli->close();
?>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td style="text-align:center" colspan="2"><input type="button" name="submit" value="<?php echo $string['ok']; ?>" class="ok" onclick="copyValue()" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" onclick="window.close()" /></td></tr>
</table>
</form>

</body>
</html>
