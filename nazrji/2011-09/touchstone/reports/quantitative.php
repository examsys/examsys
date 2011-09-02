<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/media.inc';

  function displayQuestion($q_no, $q_id, $theme, $scenario, $leadin, $q_type, $correct, $q_media, $q_media_width, $q_media_height, $options, $log, $correct_buf, $screen, $candidates) {
      global $old_likert_scale, $old_score_method, $table_on;
      if ($q_type != 'likert' and $q_type != 'textbox' and $table_on == 1) {
        echo "</table>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n"; 
      }
      if ($q_type != 'textbox') {
        if ($theme != '') {
          echo "<tr><td colspan=\"2\"><h1 style=\"marging-left:10px\">$theme</h1></td></tr>\n";
          $old_likert_scale = '';
        }
        if ($q_type != 'likert') echo "<tr>\n";
      }
      if ($q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'textbox') {
        if ($scenario != '' and $q_type != 'likert') {
          echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td><p>$scenario</p>\n";
          echo "<p>$leadin</p>\n";
          if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling') {
            echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height,$q_no) . "</p>\n";
          }
          if ($q_type != 'hotspot' and $q_type != 'labelling') echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
        } elseif ($q_type != 'likert') {
          echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td><p>$leadin</p>\n";
          if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling') {
            echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height,$q_no) . "</p>\n";
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
          case 'calculation':
            echo "<tr><td><strong>Correct</strong></td><td><strong>" . $log[$screen][$q_id][1]['correct'] . "</strong></td></tr>\n";
            echo "<tr><td>Within tolerance&nbsp;&nbsp;&nbsp;</td><td>" . $log[$screen][$q_id][1]['tolerance'] . "</td></tr>\n";
            echo "<tr><td>Incorrect</td><td>" . $log[$screen][$q_id][1]['incorrect'] . "</td></tr>\n";
            if ($log[$screen][$q_id][1]['u'] == '') $log[$screen][$q_id][1]['u'] = 0;
            echo "<tr><td style=\"color:#808080\">Unanswered</td><td>" . $log[$screen][$q_id][1]['u'] . "</td></tr>\n";
            break;
          case 'dichotomous':
            if ($old_score_method == 'YN_Positive' or $old_score_method == 'YN_NegativeAbstain') {
              echo "<tr><td style=\"font-weight:bold; text-align:center\">Yes</td><td style=\"font-weight:bold; text-align:center\">No</td><td style=\"font-weight:bold; text-align:center\">Abstain</td><td></td></tr>\n";
            } else {
              echo "<tr><td style=\"font-weight:bold; text-align:center\">True</td><td style=\"font-weight:bold; text-align:center\">False</td><td style=\"font-weight:bold; text-align:center\">Abstain</td><td></td></tr>\n";
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
    <div align="center">
      <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="<?php echo $q_media_width + 35; ?>" height="<?php echo $q_media_height; ?>" id="label_answer" align="middle">
      <param name="allowScriptAccess" value="sameDomain" />
      <param name="movie" value="label_analysis.swf" />
      <param name="quality" value="high" />
      <param name="bgcolor" value="#ffffff" />
      <param name="FlashVars" value="imageName=<?php echo $q_media; ?>&labels=<?php echo $correct; ?>" />
      <embed src="label_analysis.swf" FlashVars="imageName=<?php echo $q_media; ?>&labels=<?php echo $correct; ?>" quality="high" bgcolor="#ffffff" width="<?php echo $q_media_width + 35; ?>" height="<?php echo $q_media_height; ?>" name="label_answer" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
      </object>
    </div>
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
            $old_size = substr_count($old_likert_scale,'|');
            $current_properties = explode('|',$old_score_method);
            $new_size = substr_count($old_score_method,'|');
            if ($current_properties[$new_size] == 'true') {
              $na = true;
            } else {
              $na = false;
            }
            if ($old_likert_scale != $old_score_method  or $table_on == 0 ) {
              if ($table_on == 1) echo "</table>\n";
              echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"margin-left:10px; margin-right:10px\">\n";
              echo '<tr><td></td><td></td>';
              if ($na == true) echo '<td style="vertical-align:bottom; text-align:center" colspan="2">N/A</td>';
              for ($point=1; $point<=$new_size; $point++) {
                echo "<td style=\"vertical-align:bottom; text-align:center\" colspan=\"2\"><strong>" . $current_properties[$point - 1] . "</strong></td>";
              }
              echo "<td style=\"vertical-align:bottom; color:#808080\" colspan=\"2\">Unanswered</td><td style=\"vertical-align:bottom\">Mean</td></tr>\n";
              $table_on = 1;
            }
            echo "<tr><td class=\"figures\">$q_no.</td><td>$leadin</td>";
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
            $old_likert_scale = $old_score_method;
            break;
          case 'hotspot':
            ?>
            <div align="center">
              <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" id="hotspot<?php echo $q_no; ?>" width="<?php echo ($q_media_width + 2); ?>" height="<?php echo ($q_media_height + 40); ?>" align="middle">
              <param name="allowScriptAccess" value="sameDomain" />
              <param name="movie" value="hotspot_analysis.swf" />
              <param name="quality" value="high" />
              <param name="bgcolor" value="#ffffff" />
              <param name="FlashVars" value="imageName=<?php echo $q_media; ?>&config=<?php echo $correct; ?>&answers=<?php echo $log[$screen][$q_id][1]['coords']; ?>" />
              <embed src="hotspot_analysis.swf" FlashVars="imageName=<?php echo $q_media; ?>&config=<?php echo $correct; ?>&answers=<?php echo $log[$screen][$q_id][1]['coords']; ?>" quality="high" bgcolor="#ffffff" width="<?php echo ($q_media_width + 2); ?>" height="<?php echo ($q_media_height + 40); ?>" swLiveConnect=true id="hotspot<?php echo $q_no; ?>" name="hotspot<?php echo $q_no; ?>" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />
              </object>
            </div>
            <?php
            echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
            echo "<tr><td class=\"figures\">Correct</td><td>" . $log[$screen][$q_id][1][1] . "</td></tr>\n";
            echo "<tr><td class=\"figures\">Incorrect</td><td>" . $log[$screen][$q_id][1][0] . "</td></tr>\n";
            if ($log[$screen][$q_id][1]['u'] == '') $log[$screen][$q_id][1]['u'] = 0;
            echo "<tr><td class=\"figures\" style=\"color:#808080\">Unanswered</td><td style=\"color:#808080\">" . $log[$screen][$q_id][1]['u'] . "</td></tr>\n";
            break;
          case 'mcq':
            $i = 0;
            foreach ($options as $individual_option) {
              $i++;
              echo "<tr>";
              if ($log[$screen][$q_id][1][$i] == '') {
                echo "<td class=\"figures\">0</td><td>(0%)</td><td>$individual_option</td></tr>\n";
              } else {
                echo "<td class=\"figures\">" . $log[$screen][$q_id][1][$i] . "</td><td>(" . round(($log[$screen][$q_id][1][$i]/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
              }
            }
            if (!isset($log[$screen][$q_id][1]['u'])) {
              echo "<tr style=\"color:#808080\"><td class=\"figures\">0</td><td>(0%)</td><td>&lt;unanswered&gt;</td></tr>\n";
            } else {
              echo "<tr style=\"color:#808080\"><td class=\"figures\">" . $log[$screen][$q_id][1]['u'] . "</td><td>(" . round(($log[$screen][$q_id][1]['u']/$candidates)*100) . "%)</td><td>&lt;unanswered&gt;</td></tr>\n";
            }
            break;
          case 'mrq':
            $i = 0;
            foreach ($options as $individual_option) {
              $i++;
              if ($log[$screen][$q_id][$i]['y'] == '') $log[$screen][$q_id][$i]['y'] = 0;
              echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i]['y'] . "</td><td>(" . round(($log[$screen][$q_id][$i]['y']/$candidates)*100) . "%)</td><td>$individual_option</td></tr>\n";
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
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i][9990] . "</td><td>(" . number_format(($log[$screen][$q_id][$i][9990]/$candidates)*100,0) . "%)</td><td></td><td>N/A</td><td style=\"width:50%\">&nbsp;</td></tr>";
              }
              if (isset($log[$screen][$q_id][$i]['u'])) {
                echo "<tr><td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "</td><td>(" . number_format(($log[$screen][$q_id][$i]['u']/$candidates)*100,0) . "%)</td><td></td><td style=\"color:#808080\">&lt;unanswered&gt;</td><td style=\"width:50%\">&nbsp;</td></tr>";
              } else {
                echo "<tr><td class=\"figures\">0</td><td>(0%)</td><td></td><td style=\"color:#808080\">&lt;unanswered&gt;</td><td style=\"width:50%\">&nbsp;</td></tr>";
              }
              echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
              $i++;
            }
            break;
          case 'timedate':
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
        echo "<td style=\"color:#808080\">unanswered</td></tr>\n";
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
          echo "<p align=\"center\">" . display_media($tmp_media_array[0],$tmp_media_width_array[0],$tmp_media_height_array[0],$q_id) . "</p>\n";
        }
        for ($i=1; $i<=(substr_count($scenario,'|')+1); $i++) {
          echo "<li>\n";
          if ($tmp_media_array[$i] != '') {
            echo "<p>" . display_media($tmp_media_array[$i],$tmp_media_width_array[$i],$tmp_media_height_array[$i],$q_id . '_' . $i) . "</p>\n";
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
            echo "<tr style=\"color:#808080\"><td class=\"figures\">" . $log[$screen][$q_id][$i]['u'] . "&nbsp;(" . round(($log[$screen][$q_id][$i]['u']/$candidates)*100) . "%)</td><td>&lt;unanswered&gt;</td></tr>\n";
          } else {
            echo "<tr style=\"color:#808080\"><td class=\"figures\">0</td><td>&lt;unanswered&gt;</td></tr>\n";
          }
          echo "</table></p></li>\n";
        }
        echo "</ol>\n";
      }
    echo "</td></tr>\n";  
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Quantitative Report</title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
h1 {margin-left:15px; font-family:Arial,sans-serif; font-size:18pt; color:#316AC5}
p {margin-right:15px}
td {vertical-align:top}
.figures {text-align:right; width:60px}
.q_no {text-align:right; width:40px}
.h {background-color:#F1F5FB; color:black}
.breadcrumb {margin-left:10px; font-size:90%}
.breadcrumb a:link {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:visited {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:hover {color:blue; text-decoration:underline; cursor:pointer}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
</head>

<body>
<?php
  $result = $mysqli->prepare("SELECT COUNT(question) AS question_no, paper_title FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND papers.question=questions.q_id AND q_type!='info' AND paper=? GROUP BY property_id");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($number_of_questions, $paper);
  $result->fetch();
  $result->close();

  $exclude = '';
  if ($_GET['complete'] == 1) {
    $result = $mysqli->prepare("SELECT userID, COUNT(id) AS answer_no FROM log3 WHERE q_paper=? AND started>=? AND started<=? GROUP BY userID");
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
    $result->bind_result($tmp_userID, $answer_no);
    while ($row = $result->fetch()) {
      if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
        $exclude .= ' AND log3.userID!=' . $tmp_userID;
      }
    }
    $result->close();
  }

  $log_array = array();
  $hits = 0;
  // Capture the log data first.
  if ($_GET['repdegree'] == 'Staff') {
    $result = $mysqli->prepare("SELECT DISTINCT log3.userID, log3.q_id, user_answer, q_type, screen, score_method FROM (log3, questions, users) WHERE log3.q_id=questions.q_id AND q_paper=? AND users.id=log3.userID AND (users.roles LIKE 'Staff%' OR users.roles LIKE '%SysAdmin%')$exclude AND started>=? AND started<=?");
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
  } else {
    $result = $mysqli->prepare("SELECT DISTINCT log3.userID, log3.q_id, user_answer, q_type, screen, score_method FROM (log3, questions, users) WHERE log3.q_id=questions.q_id AND q_paper=? AND users.id=log3.userID AND (users.roles='Student' OR users.roles='graduate')$exclude AND grade LIKE ? AND started>=? AND started<=?");
    $result->bind_param('isss', $_GET['paperID'], $_GET['repdegree'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
  }
  $result->bind_result($tmp_userID, $question_ID, $tmp_answer, $q_type, $screen, $score_method);
  while ($row = $result->fetch()) {
    $tmp_answer = str_replace('&','&amp;',$tmp_answer);
    switch ($q_type) {
      case 'blank':
        $tmp_answer_parts = array();
        $tmp_answer_parts = explode('|',$tmp_answer);
        $i = 0;
        foreach ($tmp_answer_parts as $tmp_individual_answer) {
          $i++;
          if ($tmp_individual_answer == 'u') {
            if (isset($log_array[$screen][$question_ID][$i]['u'])) {
              $log_array[$screen][$question_ID][$i]['u']++;
            } else {
              $log_array[$screen][$question_ID][$i]['u'] = 1;
            }
          } else {
            if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_answer])) {
              $log_array[$screen][$question_ID][$i][$tmp_individual_answer]++;
            } else {
              $log_array[$screen][$question_ID][$i][$tmp_individual_answer] = 1;
            }
          }
        }
        break;
      case 'calculation':
        $tmp_score_method = array();
        $tmp_score_method = explode(',',$score_method);
        $tolerance = $tmp_score_method[1];
        $tmp_first_split = explode('|', $tmp_answer);
        if ($tmp_first_split[0] == $tmp_first_split[1]) {
          if (isset($log_array[$screen][$question_ID][1]['correct'])) {
            $log_array[$screen][$question_ID][1]['correct']++;
          } else {
            $log_array[$screen][$question_ID][1]['correct'] = 1;
          }
        } else {
          if ($tmp_first_split[0] == '') {
            if (isset($log_array[$screen][$question_ID][1]['u'])) {
              $log_array[$screen][$question_ID][1]['u']++;
            } else {
              $log_array[$screen][$question_ID][1]['u'] = 1;
            }
          } elseif (abs($tmp_first_split[0] - $tmp_first_split[1]) <= $tolerance) {
            if (isset($log_array[$screen][$question_ID][1]['tolerance'])) {
              $log_array[$screen][$question_ID][1]['tolerance']++;
            } else {
              $log_array[$screen][$question_ID][1]['tolerance'] = 1;
            }
          } else {
            if (isset($log_array[$screen][$question_ID][1]['incorrect'])) {
              $log_array[$screen][$question_ID][1]['incorrect']++;
            } else {
              $log_array[$screen][$question_ID][1]['incorrect'] = 1;
            }
          }
        }
        break;
      case 'dichotomous':
        for ($i=0; $i<strlen($tmp_answer); $i++) {
          $tmp_individual_answer = substr($tmp_answer, $i, 1);
          if (isset($log_array[$screen][$question_ID][$i+1][$tmp_individual_answer])) {
            $log_array[$screen][$question_ID][$i+1][$tmp_individual_answer]++;
          } else {
            $log_array[$screen][$question_ID][$i+1][$tmp_individual_answer] = 1;
          }
        }
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $tmp_answer);
        $tmp_second_split = explode('|', $tmp_first_split[1]);
        $sections = count($tmp_second_split);
        for ($i=2; $i<=count($tmp_second_split);$i+=4) {
          $x_coord = $tmp_second_split[$i-2];
          $y_coord = $tmp_second_split[$i-1];
          $tmp_individual_answer = trim($tmp_second_split[$i]);
          $element = $x_coord . 'x' . $y_coord;
          if (isset($log_array[$screen][$question_ID][$element][$tmp_individual_answer])) {
            $log_array[$screen][$question_ID][$element][$tmp_individual_answer]++;
          } else {
            $log_array[$screen][$question_ID][$element][$tmp_individual_answer] = 1;
          }
        }
        break;
      case 'likert':
        if (isset($log_array[$screen][$question_ID][1][$tmp_answer])) {
          $log_array[$screen][$question_ID][1][$tmp_answer]++;
        } else {
          $log_array[$screen][$question_ID][1][$tmp_answer] = 1;
        }
        break;
      case 'hotspot':
        if (substr($tmp_answer,0,1) == '1') {
          if (isset($log_array[$screen][$question_ID][1]['1'])) {
            $log_array[$screen][$question_ID][1]['1']++;
          } else {
            $log_array[$screen][$question_ID][1]['1'] = 1;
          }
        } elseif (substr($tmp_answer,0,1) == '0') {
          if (isset($log_array[$screen][$question_ID][1]['0'])) {
            $log_array[$screen][$question_ID][1]['0']++;
          } else {
            $log_array[$screen][$question_ID][1]['0'] = 1;
          }
        } else {
          if (isset($log_array[$screen][$question_ID][1]['u'])) {
            $log_array[$screen][$question_ID][1]['u']++;
          } else {
            $log_array[$screen][$question_ID][1]['u'] = 1;
          }
        }
        if ($log_array[$screen][$question_ID][1]['coords'] == '') {
          $log_array[$screen][$question_ID][1]['coords'] = substr($tmp_answer,2);
        } else {
          $log_array[$screen][$question_ID][1]['coords'] .= ';' . substr($tmp_answer,2);
        }
        break;
      case 'mcq':
        if (substr($tmp_answer,0,5) == 'other') {
          $log_array[$screen][$question_ID][1]['other'][] = substr($tmp_answer,6);
        } elseif ($tmp_answer == 0) {
          if (isset($log_array[$screen][$question_ID][1]['u'])) {
            $log_array[$screen][$question_ID][1]['u']++;
          } else {
            $log_array[$screen][$question_ID][1]['u'] = 1;
          }
        } else {
          if (isset($log_array[$screen][$question_ID][1][$tmp_answer])) {
            $log_array[$screen][$question_ID][1][$tmp_answer]++;
          } else {
            $log_array[$screen][$question_ID][1][$tmp_answer] = 1;
          }
        }
        break;
      case 'mrq':
        for ($i=0; $i<strlen($tmp_answer); $i++) {
          $tmp_individual_answer = substr($tmp_answer, $i, 1);
          if (isset($log_array[$screen][$question_ID][$i+1][$tmp_individual_answer])) {
            $log_array[$screen][$question_ID][$i+1][$tmp_individual_answer]++;
          } else {
            $log_array[$screen][$question_ID][$i+1][$tmp_individual_answer] = 1;
          }
        }
        break;
      case 'extmatch':
        $tmp_answer_parts = array();
        $tmp_answer_parts = explode('|',$tmp_answer);
        $i = 0;
        foreach ($tmp_answer_parts as $tmp_individual_answer) {
          $i++;
          $tmp_sub_parts = array();
          $tmp_sub_parts = explode('|',$tmp_individual_answer);
          foreach ($tmp_sub_parts as $tmp_individual_part) {
            if ($tmp_individual_answer == 'u') {
              if (isset($log_array[$screen][$question_ID][$i]['u'])) {
                $log_array[$screen][$question_ID][$i]['u']++;
              } else {
                $log_array[$screen][$question_ID][$i]['u'] = 1;
              }
            } else {
              if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_part])) {
                $log_array[$screen][$question_ID][$i][$tmp_individual_part]++;
              } else {
                $log_array[$screen][$question_ID][$i][$tmp_individual_part] = 1;
              }
            }
          }
        }
        break;
      case 'matrix':
        $tmp_answer_parts = array();
        $tmp_answer_parts = explode('|',$tmp_answer);
        $i = 0;
        foreach ($tmp_answer_parts as $tmp_individual_answer) {
          $i++;
          if ($tmp_individual_answer == 'u' or $tmp_individual_answer == '') {
            if (isset($log_array[$screen][$question_ID][$i]['u'])) {
              $log_array[$screen][$question_ID][$i]['u']++;
            } else {
              $log_array[$screen][$question_ID][$i]['u'] = 1;
            }
          } else {
            if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_answer])) {
              $log_array[$screen][$question_ID][$i][$tmp_individual_answer]++;
            } else {
              $log_array[$screen][$question_ID][$i][$tmp_individual_answer] = 1;
            }
          }
        }
        break;
      case 'rank':
        $tmp_answer_parts = array();
        $tmp_answer_parts = explode(',',$tmp_answer);
        $i = 0;
        foreach ($tmp_answer_parts as $tmp_individual_answer) {
          if ($tmp_individual_answer == '9999') {
            if (isset($log_array[$screen][$question_ID][$i]['u'])) {
              $log_array[$screen][$question_ID][$i]['u']++;
            } else {
              $log_array[$screen][$question_ID][$i]['u']++;
            }
          } else {
            if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_answer])) {
              $log_array[$screen][$question_ID][$i][$tmp_individual_answer]++;
            } else {
              $log_array[$screen][$question_ID][$i][$tmp_individual_answer] = 1;
            }
          }
          $i++;
        }
        break;
      case 'textbox':
        $log_array[$screen][$question_ID][1]['other'][] = $tmp_answer;
        break;
      case 'timedate':
        $log_array[$screen][$question_ID][1]['other'][] = $tmp_answer;
        break;
    }
  }
  $result->close();

  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo '<tr><td class="h"><div class="breadcrumb"><a href="../index.php">Home</a>';
  if ($folder != '') echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  if (isset($_GET['module']) and $_GET['module'] != '') echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';
  echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">Quantitative Report</span></td><td class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(33); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
  echo '<tr style="height:4px"><td valign="top" colspan="11"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>';
  echo "\n</table>\n";

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
  
  $result = $mysqli->prepare("SELECT screen, q_id, q_type, theme, scenario, leadin, option_text, score_method, q_media, q_media_width, q_media_height, correct FROM papers, questions, options WHERE papers.question=questions.q_id AND questions.q_id=options.o_id AND papers.paper=? ORDER BY screen, display_pos, id_num");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($screen, $q_id, $q_type, $theme, $scenario, $leadin, $option_text, $score_method, $q_media, $q_media_width, $q_media_height, $correct);
  while ($row = $result->fetch()) {
    // Replace & characters.
    $theme = str_replace('&','&amp;',$theme);
    $scenario = str_replace('&','&amp;',$scenario);
    $leadin = str_replace('&','&amp;',$leadin);
    $option_text = str_replace('&','&amp;',$option_text);

    if ($old_q_id != $q_id and $old_q_id > 0) {   // New question.
      if ($old_q_type == 'likert') {
        $options_buffer['n/a'] = 'n/a';
        $likert_properties = explode('|',$old_score_method);
        for ($i=1; $i<=substr_count($old_score_method,'|'); $i++) {
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
        echo "<tr><td colspan=\"2\">($respondents Respondents)</td></tr>\n";
        $display_respondents = 0;
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
          echo '<br /><table cellpadding="2" cellspacing="0" border="0" style="width:100%; height:70px; border-top:1px solid #B5C4DF; background-image:url(\'../artwork/screen_no_background.gif\'); background-repeat:repeat-x">';
          echo "<tr>\n<td colspan=\"2\" style=\"padding-left:20px; vertical-align:top; font-size:90%; font-weight:bold; color:#15428B\">Screen&nbsp;$screen</td>\n</tr>\n";
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
  }
  $result->close();
  
  //$question_no++;
  if ($old_q_type == 'likert') {
    $options_buffer['n/a'] = 'n/a';
    $likert_properties = explode('|',$old_score_method);
    for ($i=1; $i<=substr_count($old_score_method,'|'); $i++) {
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
