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

  require '../include/staff_auth.inc';
  require '../include/survey_quantitative.inc.php';
  require '../include/media.inc';

  function displayQuestion($q_no, $q_id, $theme, $scenario, $leadin, $q_type, $correct, $q_media, $q_media_width, $q_media_height, $options, $log, $correct_buf, $screen, $candidates) {
      global $old_likert_scale, $old_score_method, $old_display_method, $table_on, $string;
      
      if ($q_type != 'likert' and $q_type != 'textbox' and $table_on == 1) {
        echo "</table>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
        $old_likert_scale = '';
      }
      if ($q_type != 'textbox') {
        if ($theme != '') {
          $cols = substr_count($old_likert_scale, '|');
          if ($cols > 0) {
            $cols += 5;
          } else {
            $cols = 2;
          }
          echo "<tr><td colspan=\"$cols\"><h1 style=\"marging-left:10px\">$theme</h1></td></tr>\n";
          $old_likert_scale = '';
        }
        if ($q_type != 'likert') echo "<tr>\n";
      }
      if ($q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'textbox') {
        if ($scenario != '' and $q_type != 'likert') {
          echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$scenario<br /><br />\n";
          echo $leadin;
          if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling') {
            echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height, '') . "</p>\n";
          }
          if ($q_type != 'hotspot' and $q_type != 'labelling') echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
        } elseif ($q_type != 'likert') {
          echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin\n";
          if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling') {
            echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height, '') . "</p>\n";
          }
          if ($q_type != 'hotspot' and $q_type != 'labelling') echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
        }
        switch ($q_type) {
          case 'blank':
            echo '<p>';
            $blank_details = array();
            $blank_details = explode('[blank',$options[0]);
            $array_size = count($blank_details);
            $blank_count = 0;
            while ($blank_count < $array_size) {
              if (strpos($blank_details[$blank_count],'[/blank]') === false) {
                echo $blank_details[$blank_count];
              } else {
                $end_start_tag = strpos($blank_details[$blank_count],']');
                $start_end_tag = strpos($blank_details[$blank_count],'[/blank]');
                $blank_options = substr($blank_details[$blank_count],($end_start_tag+1),($start_end_tag-1));
                $remainder = substr($blank_details[$blank_count], ($start_end_tag+8));
                echo '<span style="color:#800000; font-weight:bold">[blank]</span>';
                $options_array = array();
                $options_array = explode(',',$blank_options);
                $i = 0;
                foreach ($options_array as $individual_blank_option) {
                  if ($log[$screen][$q_id][$blank_count+1][$individual_blank_option] == '') $log[$screen][$q_id][$blank_count+1][$individual_blank_option] = 0;
                  if ($i == 0) {
                    echo '<strong>' . $individual_blank_option . '=' . $log[$screen][$q_id][$blank_count+1][$individual_blank_option] . '</strong>';
                  } else {
                    echo ', ' . $individual_blank_option . '=' . $log[$screen][$q_id][$blank_count+1][$individual_blank_option];
                  }
                  $i++;
                }
                echo '<span style="color:#800000; font-weight:bold">[/blank]</span>' . $remainder;
              }
              $blank_count++;
            }
            echo '</p>';
            break;
          case 'dichotomous':
            if ($old_display_method == 'YN_Positive' or $old_display_method == 'YN_NegativeAbstain') {
              echo "<tr><td style=\"font-weight:bold; text-align:center\">" . $string['yes'] . "</td><td style=\"font-weight:bold; text-align:center\">" . $string['no'] . "</td><td style=\"font-weight:bold; text-align:center\">" . $string['abstain'] . "</td><td></td></tr>\n";
            } else {
              echo "<tr><td style=\"font-weight:bold; text-align:center\">" . $string['true'] . "</td><td style=\"font-weight:bold; text-align:center\">" . $string['false'] . "</td><td style=\"font-weight:bold; text-align:center\">" . $string['abstain'] . "</td><td></td></tr>\n";
            }
            $i = 0;
            foreach ($options as $individual_option) {
              $i++;
              if ($log[$screen][$q_id][$i]['u'] == '') $log[$screen][$q_id][$i]['u'] = 0;
              if ($log[$screen][$q_id][$i]['t'] == '') $log[$screen][$q_id][$i]['t'] = 0;
              if ($log[$screen][$q_id][$i]['f'] == '') $log[$screen][$q_id][$i]['f'] = 0;
              if ($correct_buf[$i-1] == 't') {
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i]['t'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['t']/$candidates)*100) . "%)</td><td class=\"figures\">" . $log[$screen][$q_id][$i]['f'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['f']/$candidates)*100) . "%)</td><td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['u']/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
              } elseif ($correct_buf[$i-1] == 'f') {
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i]['t'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['t']/$candidates)*100) . "%)</td><td class=\"figures\">" . $log[$screen][$q_id][$i]['f'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['f']/$candidates)*100) . "%)</td><td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['u']/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
              } else {
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i]['t'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['t']/$candidates)*100) . "%)</td><td class=\"figures\">" . $log[$screen][$q_id][$i]['f'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['f']/$candidates)*100) . "%)</td><td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['u']/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
              }
            }
            break;
          case 'labelling':
?>
    <br />
<?php
            echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
            $i = 1;
            foreach ($correct_buf as $individual_coord) {
              echo "<tr><td colspan=\"3\">Placeholder " . chr($i + 64) . ".</td></tr>\n";
              $option_no = 1;
              foreach ($options as $individual_option) {
                $individual_option = trim($individual_option);
                if ($option_no == $i) {
                  if ($log[$screen][$q_id][$individual_coord][$individual_option] == '') {
                    echo "<tr><td style=\"width: 20px\">&nbsp;</td><td><strong>$individual_option</strong></td><td><strong>0</strong></td></tr>\n";
                  } else {
                    echo "<tr><td></td><td><strong>$individual_option</strong></td><td><strong>" . $log[$screen][$q_id][$individual_coord][$individual_option] . "</strong></td></tr>\n";
                  }
                } else {
                  if ($log[$screen][$q_id][$individual_coord][$individual_option] == '') {
                    echo "<tr><td></td><td>$individual_option</td><td>0</td></tr>\n";
                  } else {
                    echo "<tr><td></td><td>$individual_option</td><td>" . $log[$screen][$q_id][$individual_coord][$individual_option] . "</td></tr>\n";
                  }
                }
                $option_no++;
              }
              echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
              $i++;
            }
            break;
          case 'likert':
            $old_size = substr_count($old_likert_scale, '|');
            $current_properties = explode('|', $old_display_method);
            $new_size = substr_count($old_display_method,'|');
            if ($current_properties[$new_size] == 'true') {
              $na = true;
            } else {
              $na = false;
            }
            if ($old_likert_scale != $old_display_method or $table_on == 0) {
              if ($table_on == 1) echo "</table>\n";
              echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"margin-right:10px\">\n";
              echo '<tr><td></td><td></td>';
              if ($na == true) echo '<td style="vertical-align:bottom; text-align:center" colspan="2">' . $string['na'] . '</td>';
              for ($point=1; $point<=$new_size; $point++) {
                echo "<td style=\"vertical-align:bottom; text-align:center\" colspan=\"2\"><strong>" . $current_properties[$point - 1] . "</strong></td>";
              }
              echo "<td style=\"vertical-align:bottom; color:#808080\" colspan=\"2\">" . $string['unanswered'] . "</td><td style=\"vertical-align:bottom\">" . $string['mean'] . "</td></tr>\n";
              $table_on = 1;
            }
            echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin</td>";
            $i = 0;
            $sub_total = 0;
            foreach ($options as $individual_option) {
              $i++;
              if ($i > 1 or $na == true) {
                if (!isset($log[$screen][$q_id][1][$individual_option])) {
                  echo "<td class=\"figures\">0</td><td>(0%)</td>\n";
                } else {
                  echo "<td class=\"figures\">" . $log[$screen][$q_id][1][$individual_option] . "</td><td>(" . round(($log[$screen][$q_id][1][$individual_option]/$candidates)*100) . "%)</td>\n";
                }
                if ($individual_option >= 1 and $individual_option <= 10) {
                  if (isset($log[$screen][$q_id][1][$individual_option])) {
                    $sub_total += $individual_option * $log[$screen][$q_id][1][$individual_option];
                  }
                }
              }
            }
            if (isset($log[$screen][$q_id][1]['n/a'])) {
              $unanswered = $log[$screen][$q_id][1]['n/a'];
            } else {
              $unanswered = 0;
            }
            if (!isset($log[$screen][$q_id][1]['u'])) {
              echo "<td class=\"figures\" style=\"color:#808080\">0</td><td style=\"color:#808080\">(0%)</td>";
            } else {
              $unanswered += $log[$screen][$q_id][1]['u'];
              if ($candidates == 0) {
                echo "<td class=\"figures\" style=\"color:#808080\">" . $log[$screen][$q_id][1]['u'] . "</td><td style=\"color:#808080\">(0%)</td>";
              } else {
                echo "<td class=\"figures\" style=\"color:#808080\">" . $log[$screen][$q_id][1]['u'] . "</td><td style=\"color:#808080\">(" . round(($log[$screen][$q_id][1]['u']/$candidates)*100) . "%)</td>";
              }
            }
            if (($candidates-$unanswered) == 0) {
              echo "<td class=\"figures\">&nbsp;</td></tr>\n";
            } else {
              echo "<td class=\"figures\">" . number_format($sub_total/($candidates-$unanswered),1) . "</td></tr>\n";
            }
            $old_likert_scale = $old_display_method;
            break;
          case 'hotspot':
            ?>
            <?php
            echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
            echo "<tr><td class=\"figures\">Correct</td><td>" . $log[$screen][$q_id][1][1] . "</td></tr>\n";
            echo "<tr><td class=\"figures\">Incorrect</td><td>" . $log[$screen][$q_id][1][0] . "</td></tr>\n";
            if ($log[$screen][$q_id][1]['u'] == '') $log[$screen][$q_id][1]['u'] = 0;
            echo "<tr><td class=\"figures\" style=\"color:#808080\">" . $string['unanswered'] . "</td><td style=\"color:#808080\">" . $log[$screen][$q_id][1]['u'] . "</td></tr>\n";
            break;
          case 'mcq':
            $i = 0;
            foreach ($options as $individual_option) {
              $i++;
              echo "<tr>";
              if (!isset($log[$screen][$q_id][1][$i]) or $log[$screen][$q_id][1][$i] == '') {
                echo "<td class=\"figures\">0</td><td>(0%)</td><td>$individual_option</td></tr>\n";
              } else {
                echo "<td class=\"figures\">" . $log[$screen][$q_id][1][$i] . "</td><td>(" . round(($log[$screen][$q_id][1][$i]/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
              }
            }
            if (!isset($log[$screen][$q_id][1]['u'])) {
              echo "<tr style=\"color:#808080\"><td class=\"figures\">0</td><td>(0%)</td><td>" . $string['unanswered'] . "</td></tr>\n";
            } else {
              echo "<tr style=\"color:#808080\"><td class=\"figures\">" . $log[$screen][$q_id][1]['u'] . "</td><td>(" . round(($log[$screen][$q_id][1]['u']/$candidates)*100) . "%)</td><td>" . $string['unanswered'] . "</td></tr>\n";
            }
            break;
          case 'mrq':
            $i = 0;
            foreach ($options as $individual_option) {
              $i++;
              if (!isset($log[$screen][$q_id][$i]['y']) or $log[$screen][$q_id][$i]['y'] == '') $log[$screen][$q_id][$i]['y'] = 0;
              echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i]['y'] . "</td><td>(" . round(($log[$screen][$q_id][$i]['y']/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
            }
            $q_option_no = count($options);
            $log_option_no = count($log[$screen][$q_id]);

            if ($log_option_no > $q_option_no) {
              foreach ($log[$screen][$q_id] as $key=>$value) {
                if ($key > $q_option_no) {
                  foreach ($value as $text=>$number) {
                    echo "<tr><td class=\"figures\">$number</td><td>(" . round(($number/$candidates)*100) . "%)</td><td>$text</td></tr>\n";
                  }
                }
              }
            }
            break;
          case 'rank':
            $old_likert_scale = '';
            $rank_no = count($correct_buf);

            $i = 0;
            $require_na = false;
            foreach ($options as $individual_option) {
              $i++;
              if (isset($log[$screen][$q_id][$i]['correct']) and $log[$screen][$q_id][$i]['correct'] == 9990) $require_na = true;
            }

            $i = 0;
            foreach ($options as $individual_option) {
              echo "<tr><td colspan=\"4\">$individual_option</td></tr>\n";
              for ($rank_position=1; $rank_position<=$rank_no; $rank_position++) {
                if (isset($log[$screen][$q_id][$i][$rank_position]) and  $log[$screen][$q_id][$i][$rank_position] == '') $log[$screen][$q_id][$i][$rank_position] = 0;
                if (isset($log[$screen][$q_id][$i][$rank_position])) {
                  echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i][$rank_position] . "</td><td>(" . number_format(($log[$screen][$q_id][$i][$rank_position]/$candidates)*100,0) . "%)</td><td></td><td>$rank_position";
                } else {
                  echo "<tr><td class=\"figures\">0</td><td>(0%)</td><td></td><td>$rank_position";
                }
                if ($rank_position == 1) {
                  echo 'st';
                } elseif ($rank_position == 2) {
                  echo 'nd';
                } elseif ($rank_position == 3) {
                  echo 'rd';
                } else {
                  echo 'th';
                }
                echo "</td><td style=\"width:50%\">&nbsp;</td></tr>\n";
              }
              if (isset($reqire_na) and $reqire_na == true) {
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i][9990] . "</td><td>(" . number_format(($log[$screen][$q_id][$i][9990]/$candidates)*100,0) . "%)</td><td></td><td>" . $string['na'] . "</td><td style=\"width:50%\">&nbsp;</td></tr>";
              }
              if (isset($log[$screen][$q_id][$i]['u'])) {
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "</td><td>(" . number_format(($log[$screen][$q_id][$i]['u']/$candidates)*100,0) . "%)</td><td></td><td style=\"color:#808080\">" . $string['unanswered'] . "</td><td style=\"width:50%\">&nbsp;</td></tr>";
              } else {
                echo "<tr><td class=\"figures\">0</td><td>(0%)</td><td></td><td style=\"color:#808080\">" . $string['unanswered'] . "</td><td style=\"width:50%\">&nbsp;</td></tr>";
              }
              echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
              $i++;
            }
            break;
        }
        if ($q_type != 'likert') echo "</table>\n";
      } elseif ($q_type == 'matrix') {
        $tmp_media_array = explode('|',$q_media);
        $tmp_media_width_array = explode('|',$q_media_width);
        $tmp_media_height_array = explode('|',$q_media_height);
        $tmp_ext_scenarios = explode('|',$scenario);
        $tmp_answers_array = explode('|',$correct_buf[0]);
        echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td><p>$leadin</p>";
        echo "<p>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\">\n";
        echo '<tr><td>&nbsp;</td>';
        foreach ($options as $individual_option) {
          echo "<td>$individual_option</td>";
        }
        echo "<td style=\"color:#808080\">" . $string['unanswered'] . "</td></tr>\n";
        for ($i=1; $i<=(substr_count($scenario,'|')+1); $i++) {
          echo "<tr>\n";
          echo "<td>" . $tmp_ext_scenarios[$i-1] . "</td>";
          $option_no = 1;
          foreach ($options as $individual_option) {
            if (!isset($log[$screen][$q_id][$i][$option_no])) {
              echo "<td class=\"figures\">0 (0%)</td>";
            } else {
              echo "<td class=\"figures\">" . $log[$screen][$q_id][$i][$option_no] . "&nbsp;(" . number_format(($log[$screen][$q_id][$i][$option_no]/$candidates)*100,0) . "%)</td>";
            }
            $option_no++;
          }
          if (isset($log[$screen][$q_id][$i]['u'])) {
            echo "<td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "</td>";
          } else {
            echo "<td class=\"figures\">0</td>";
          }
          echo "</tr>\n";
        }
        echo "</table>\n</td></tr>\n";
      } elseif ($q_type == 'extmatch') {
        $tmp_media_array = explode('|',$q_media);
        $tmp_media_width_array = explode('|',$q_media_width);
        $tmp_media_height_array = explode('|',$q_media_height);
        $tmp_ext_scenarios = explode('|',$scenario);
        $tmp_answers_array = explode('|',$correct_buf[0]);
        echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td><p>$leadin</p>\n<ol type=\"i\">";
        if ($tmp_media_array[0] != '') {
          echo "<p align=\"center\">" . display_media($tmp_media_array[0], $tmp_media_width_array[0], $tmp_media_height_array[0], '') . "</p>\n";
        }
        for ($i=1; $i<=(substr_count($scenario,'|')+1); $i++) {
          echo "<li>\n";
          if ($tmp_media_array[$i] != '') {
            echo "<p>" . display_media($tmp_media_array[$i], $tmp_media_width_array[$i], $tmp_media_height_array[$i], '') . "</p>\n";
          }
          if ($tmp_ext_scenarios[$i-1]) echo "<p>" . $tmp_ext_scenarios[$i-1] . "</p>\n";
          echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
          $option_no = 1;
          foreach ($options as $individual_option) {
            $specific_answers = array();
            $specific_answers = explode('|', $tmp_answers_array[$i-1]);
            $answer_match = false;
            for ($x=0; $x<count($specific_answers); $x++) {
              if ($option_no == $specific_answers[$x]) $answer_match = true;
            }
            if ($answer_match == true) {
              if ($log[$screen][$q_id][$i][$option_no] == '') {
                echo "<tr><td class=\"figures\" style=\"font-weight:bold\">0</td><td style=\"font-weight:bold\">$individual_option</td></tr>\n";
              } else {
                echo "<tr><td class=\"figures\" style=\"font-weight:bold\">" . $log[$screen][$q_id][$i][$option_no] . "&nbsp;(" . round(($log[$screen][$q_id][$i][$option_no]/$candidates)*100) . "%)</td><td style=\"font-weight:bold\">$individual_option</td></tr>\n";
              }
            } else {
              if ($log[$screen][$q_id][$i][$option_no] == '') {
                echo "<tr><td class=\"figures\">0</td><td>$individual_option</td></tr>\n";
              } else {
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i][$option_no] . "&nbsp;(" . round(($log[$screen][$q_id][$i][$option_no]/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
              }
            }
            $option_no++;
          }
          if ($log[$screen][$q_id][$i]['u'] > 0) {
            echo "<tr style=\"color:#808080\"><td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['u']/$candidates)*100) . "%)</td><td>" . $string['unanswered'] . "</td></tr>\n";
          } else {
            echo "<tr style=\"color:#808080\"><td class=\"figures\">0</td><td>" . $string['unanswered'] . "</td></tr>\n";
          }
          echo "</table></p></li>\n";
        }
        echo "</ol>\n";
      }
    echo "</td></tr>\n";
  }
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['quantitativereport']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/finish.css" />
  <style type="text/css">
    body {font-size: 90%}
    h1 {margin-left: 15px; font-size: 18pt; color:#316AC5}
    table {font-size: 100%}
    p {margin-right: 15px}
    td {vertical-align: top}
    .figures {text-align: right; width: 60px}
    .q_no {text-align:right; width:40px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(33);

  $result = $mysqli->prepare("SELECT COUNT(question) AS question_no, paper_title FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND papers.question=questions.q_id AND q_type != 'info' AND paper = ? GROUP BY property_id");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($number_of_questions, $paper);
  $result->fetch();
  $result->close();

  $exclude = '';
  if ($_GET['complete'] == 1) {
    $result = $mysqli->prepare("SELECT userID, COUNT(id) AS answer_no FROM log3 WHERE q_paper = ? AND started >= ? AND started <= ? GROUP BY userID");
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
    $result->bind_result($tmp_userID, $answer_no);
    while ($result->fetch()) {
      if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
        // log_metadata aliased as lm in queries below for brevity
        $exclude .= ' AND lm.userID!=' . $tmp_userID;
      }
    }
    $result->close();
  }

  $log_array = array();
  $hits = get_quantitative_log_data($_GET['paperID'], $_GET['repcourse'], $_GET['startdate'], $_GET['enddate'], $exclude, $log_array, $mysqli);

  $module_code = '';
  $module = (isset($_GET['module']) and $_GET['module'] != '') ? $_GET['module'] : '';
  if ($module != '') {
    $result = $mysqli->prepare("SELECT moduleid FROM modules WHERE id=? LIMIT 1");
    $result->bind_param('i', $module);
    $result->execute();
    $result->bind_result($module_code);
    $result->fetch();
    $result->close();
  }

  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id = ? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }
  
  
  
  
  echo "<div class=\"head_title\">\n";
  echo "<img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" />\n";

  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if ($folder != '') echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . $folder_name . '</a>';
  if ($module != '' and $module != 0) echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . $module_code . '</a>';
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';
  echo "<div class=\"page_title\">" . $string['quantitativereport'] . "</div>\n";
  echo "</div>\n";

  echo '<table cellpadding="2" cellspacing="0" border="0" width="100%">';
  // Capture the paper makeup.
  $question_no = 1;
  $display_respondents = 1;
  $respondents = 0;
  $old_q_id = 0;
  $old_screen = 0;
  $old_likert_scale = '';
  $table_on = 1;
  $options_buffer = array();
  $correct_buffer = array();

  $result = $mysqli->prepare("SELECT screen, q_id, q_type, theme, scenario, leadin, option_text, score_method, display_method, q_media, q_media_width, q_media_height, correct FROM papers, questions, options WHERE papers.question=questions.q_id AND questions.q_id=options.o_id AND papers.paper=? ORDER BY screen, display_pos, id_num");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($screen, $q_id, $q_type, $theme, $scenario, $leadin, $option_text, $score_method, $display_method, $q_media, $q_media_width, $q_media_height, $correct);
  while ($result->fetch()) {
    // Replace &nbsp; with spaces.
    $theme = str_replace('&nbsp;',' ',$theme);
    $scenario = str_replace('&nbsp;',' ',$scenario);
    $leadin = str_replace('&nbsp;',' ',$leadin);
    $option_text = str_replace('&nbsp;',' ',$option_text);

    // Replace & with code.
    $theme = str_replace('&','&amp;',$theme);
    $scenario = str_replace('&','&amp;',$scenario);
    $leadin = str_replace('&','&amp;',$leadin);
    $option_text = str_replace('&','&amp;',$option_text);

    if ($old_q_id != $q_id and $old_q_id > 0) {   // New question.
      if ($old_q_type == 'likert') {
        $options_buffer['n/a'] = 'n/a';
        $likert_properties = explode('|',$old_display_method);
        for ($i=1; $i<=substr_count($old_display_method,'|'); $i++) {
          $options_buffer[$i] = $i;
        }
      }
      if ($display_respondents == 1 and $old_q_type != 'info') { // Calculate how many candidates.
        $respondents = 0;
        $i = 1;
        foreach ($options_buffer as $individual_option) {
          if (isset($log_array[$old_screen][$old_q_id][1][$i])) {
            $respondents += $log_array[$old_screen][$old_q_id][1][$i];
          }
          $i++;
        }
        if (isset($log_array[$old_screen][$old_q_id][1]['n/a'])) $respondents += $log_array[$old_screen][$old_q_id][1]['n/a'];
        if (isset($log_array[$old_screen][$old_q_id][1]['t'])) $respondents += $log_array[$old_screen][$old_q_id][1]['t'];
        if (isset($log_array[$old_screen][$old_q_id][1]['f'])) $respondents += $log_array[$old_screen][$old_q_id][1]['f'];
        if (isset($log_array[$old_screen][$old_q_id][1]['y'])) $respondents += $log_array[$old_screen][$old_q_id][1]['y'];
        if (isset($log_array[$old_screen][$old_q_id][1]['n'])) $respondents += $log_array[$old_screen][$old_q_id][1]['n'];
        if (isset($log_array[$old_screen][$old_q_id][1]['u'])) $respondents += $log_array[$old_screen][$old_q_id][1]['u'];
        if (isset($log_array[$old_screen][$old_q_id][1]['other'])) $respondents += count($log_array[$old_screen][$old_q_id][1]['other']);
        echo "<tr><td colspan=\"2\">($respondents " . $string['respondents'] . ")</td></tr>\n";
        $display_respondents = 0;

        if ($respondents == 0) {
          exit;
        }
      }
      if ($old_q_type != 'info') {
        displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, strip_tags($old_leadin), $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $log_array, $correct_buffer, $old_screen, $respondents);
        $question_no++;
      }
      if ($old_screen < $screen) {
        $display_respondents = 1;
				if ($table_on == 1) {
					echo "</table>\n";
					$table_on = 0;
				}
        if ($screen > 1) {
				  echo '<br /><div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div>';
        }
      }
      $options_buffer = array();
      $correct_buffer = array();
    }
    if ($q_type == 'labelling') {
      $tmp_first_split = explode(';', $correct);
      $tmp_second_split = explode('|', $tmp_first_split[8]);
      for ($label_no = 4; $label_no <= 43; $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|') {
          $options_buffer[] = trim(substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|')));
          $correct_buffer[] = $tmp_second_split[$label_no-2] . 'x' . ($tmp_second_split[$label_no-1] - 25);
        }
      }
    } else {
      if ($q_type != 'likert') $options_buffer[] = $option_text;
      $correct_buffer[] = $correct;
    }
    $old_q_id = $q_id;
    $old_screen = $screen;
    $old_theme = $theme;
    $old_scenario = $scenario;
    $old_leadin = $leadin;
    $old_q_type = $q_type;
    $old_q_media = $q_media;
    $old_q_media_width = $q_media_width;
    $old_q_media_height = $q_media_height;
    $old_correct = $correct;
    $old_score_method = $score_method;
    $old_display_method = $display_method;
  }
  $result->close();

  if ($old_q_type == 'likert') {
    $options_buffer['n/a'] = 'n/a';
    $likert_properties = explode('|',$old_display_method);
    for ($i=1; $i<=substr_count($old_display_method,'|'); $i++) {
      $options_buffer[$i] = $i;
    }
  }
  if ($question_no == 1 or $display_respondents == 1) { // Calculate how many candidates.
    $respondents = 0;
    $i = 1;
    foreach ($options_buffer as $individual_option) {
      $respondents += $log_array[$old_screen][$old_q_id][1][$i];
      $i++;
    }
    if (isset($log_array[$old_screen][$old_q_id][1]['n/a'])) $respondents += $log_array[$old_screen][$old_q_id][1]['n/a'];
    if (isset($log_array[$old_screen][$old_q_id][1]['t'])) $respondents += $log_array[$old_screen][$old_q_id][1]['t'];
    if (isset($log_array[$old_screen][$old_q_id][1]['f'])) $respondents += $log_array[$old_screen][$old_q_id][1]['f'];
    if (isset($log_array[$old_screen][$old_q_id][1]['y'])) $respondents += $log_array[$old_screen][$old_q_id][1]['y'];
    if (isset($log_array[$old_screen][$old_q_id][1]['n'])) $respondents += $log_array[$old_screen][$old_q_id][1]['n'];
    if (isset($log_array[$old_screen][$old_q_id][1]['u'])) $respondents += $log_array[$old_screen][$old_q_id][1]['u'];
    if (isset($log_array[$old_screen][$old_q_id][1]['other'])) $respondents += count($log_array[$old_screen][$old_q_id][1]['other']);

    echo "<tr><td colspan=\"2\">($respondents Respondents)</td></tr>\n";
  }
  if ($old_q_type != 'info') {
    displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, strip_tags($old_leadin), $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $log_array, $correct_buffer, $old_screen, $respondents);
  }

  if ($table_on == 1) echo "</table>\n<br />\n";
  $mysqli->close();
?>
</body>
</html>
