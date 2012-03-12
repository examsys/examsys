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
* View internal and external reviewers comments
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/media.inc';
  
  $type = $_GET['type'];
  $paperID = $_GET['paperID'];
  
  function displayRank($rank_position) {
    if ($rank_position == 1) {
      $html = '1st';
    } elseif ($rank_position == 2) {
      $html = '2nd';
    } elseif ($rank_position == 3) {
      $html = '3rd';
    } elseif ($rank_position == 9990) {
      $html = $string['na'];
    } else {
      $html = $rank_position . 'th';
    }
    return $html;
  }

  function displayComments($questionID, $comments_data, $qtype, $qno) {
    global $incomplete_names, $type, $string, $language;

    $html = "<tr><td></td><td><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:98%\">\n";
    $html .= "<tr><td colspan=\"5\"><strong>" . $string[$type . 'comments'] . "$qno</strong>&nbsp;<img onclick=\"editQuestion('$qtype',$questionID,$qno)\" style=\"cursor:pointer\" src=\"../artwork/edit_icon_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['editquestion'] . "\" border=\"0\" /></td></tr>\n";
    $html .= "<tr><td class=\"reviewbar\" style=\"width:20px\">&nbsp;</td><td style=\"width:20%\" class=\"reviewbar\">" . $string['reviewer'] . "</td><td style=\"width:35%\" class=\"reviewbar\">" . $string['comment'] . "</td><td style=\"width:10%\" class=\"reviewbar\">" . $string['action'] . "</td><td style=\"width:35%\" class=\"reviewbar\">" . $string['response'] . "</td></tr>\n";
    //$html .= "<tr style=\"background-image:url('../artwork/gradient_heading.png'); background-repeat:repeat-x; background-color:#E3EFFF\"><td style=\"width:20px; border-top:1px solid #6593CF; border-bottom:1px solid #6593CF\">&nbsp;</td><td style=\"width:20%; border-top:1px solid #6593CF; border-bottom:1px solid #6593CF\">" . $string['reviewer'] . "</td><td style=\"width:35%; border-top:1px solid #6593CF; border-bottom:1px solid #6593CF\">" . $string['comment'] . "</td><td style=\"width:10%; border-top:1px solid #6593CF; border-bottom:1px solid #6593CF\">" . $string['action'] . "</td><td style=\"width:35%; border-top: 1px solid #6593CF; border-bottom:1px solid #6593CF\">" . $string['response'] . "</td></tr>\n";
    if (isset($comments_data[$questionID])) {
      foreach ($comments_data[$questionID] as $reviewer => $index) {
        $image = 'ok_comment.png';
        $status = 'OK';
        if ($comments_data[$questionID][$reviewer]['category'] == 2) {
          $image = 'minor_comment.png';
          $status = 'Minor';
        } elseif ($comments_data[$questionID][$reviewer]['category'] == 3) {
          $image = 'major_comment.png';
          $status = 'Major';
        }
        $tmp_comment = nl2br($comments_data[$questionID][$reviewer]['comment']);
        if (trim($tmp_comment) == '') {
          $tmp_comment = '<span style="color:#808080">' . $string['nocomment'] . '</span>';
          $tmp_action = '<span style="color:#808080">' . $string['nocomment'] . '</span>';
          $tmp_response = '<span style="color:#808080">' . $string['na'] . '</span>';
        } else {
          $tmp_action = $string[$comments_data[$questionID][$reviewer]['action']];
          $tmp_response = nl2br($comments_data[$questionID][$reviewer]['response']);
        }
        
        if (trim($tmp_response) == '') $tmp_response = '<span style="color:#808080">' . $string['noresponse'] . '</span>';
        $html .= "<tr class=\"$status\" style=\"border-bottom:solid 1px #E3EFFF\"><td style=\"border-bottom:solid 1px #E3EFFF\"><img src=\"../artwork/$image\" width=\"16\" height=\"16\" alt=\"$status\" /></td><td style=\"border-bottom:solid 1px #E3EFFF\">" . $comments_data[$questionID][$reviewer]['name'] . "</td><td style=\"border-bottom:solid 1px #E3EFFF\">$tmp_comment</td><td style=\"border-bottom:solid 1px #E3EFFF\">$tmp_action</td><td style=\"border-bottom:solid 1px #E3EFFF\">$tmp_response</td></tr>\n";
      }
    }
    if (isset($incomplete_names)) {
      foreach ($incomplete_names as $single_incomplete) {
        $html .= "<tr class=\"OK\" style=\"border-bottom:solid 1px #E3EFFF\"><td style=\"border-bottom:solid 1px #E3EFFF\">&nbsp;</td><td style=\"border-bottom:solid 1px #E3EFFF; color:red\">$single_incomplete</td><td style=\"border-bottom:solid 1px #E3EFFF; color:red; text-align:center\" colspan=\"3\">" . $string['notreviewed'] . "</td></tr>\n";
      }
    }
    $html .= "</table></td></tr>\n";
    
    return $html;
  }

  function displayQuestion($q_no, $q_id, $theme, $scenario, $leadin, $q_type, $correct, $q_media, $q_media_width, $q_media_height, $options, $comments, $correct_buf, $display_method, $score_method, $labelcolor, $themecolor, $std) {
    global $language, $cfg_root_path;
    
    if ($theme != '') echo "<tr><td colspan=\"2\"><h1 style=\"color:$themecolor\">$theme</h1></td></tr>\n";
    echo "<tr>\n";

    if ($q_type != 'extmatch' and $q_type != 'matrix') {
      if ($q_type == 'info') {
        echo "<td colspan=\"2\" style=\"padding-left:10px; padding-right:10px\">$leadin\n";
      } else {
        if ($scenario != '') {
          echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$scenario<br />\n";
          echo $leadin;
          if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling') {
            echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height) . "</p>\n";
          }
          if ($q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'blank') echo "<p>\n<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"margin-left:30px\">\n";
        } else {
          echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin\n";
          if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling') {
            echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height) . "</p>\n";
          }
          if ($q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'blank') echo "<p>\n<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"margin-left:30px\">\n";
        }
      }
      switch ($q_type) {
        case 'blank':
          $options[0] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$options[0]);
          $options[0] = preg_replace("| size=\"([0-9]{1,3})\"|","",$options[0]);
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

              if ($display_method == 'dropdown') {
                echo '<select>';
                $options_array = array();
                $options_array = explode(',',$blank_options);
                $i = 0;
                foreach ($options_array as $individual_blank_option) {
                  $individual_blank_option = trim($individual_blank_option);
                  if ($i == 0) {
                    echo '<option value="" selected="selected">' . $individual_blank_option . '</option>';
                  } else {
                    echo '<option value="">' . $individual_blank_option . '</option>';
                  }
                  $i++;
                }
                echo '</select>';
              } else {
                // Correct answer.
                $correct_options = explode(',' , $blank_options);
                echo '<input type="text" size="10" value="' . $correct_options[0] . '" />';
              }
              echo $remainder;
            }
            $blank_count++;
          }
          break;
        case 'calculation':
          break;
        case 'dichotomous':
          $tmp_std_array = explode(',', $std);
          $std_part = 0;
          if ($score_method == 'YN_Positive') {
            $true_label = 'Yes';
            $false_label = 'No';
          } else {
            $true_label = 'True';
            $false_label = 'False';
          }
          $i = 0;
          foreach ($options as $individual_option) {
            $i++;
            if ($correct_buf[$i-1] == 't') {
              echo "<tr><td style=\"font-weight:bold\">$true_label</td><td>$individual_option</td></tr>\n";
            } else {
              echo "<tr><td style=\"font-weight:bold\">$false_label</td><td>$individual_option</td></tr>\n";
            }
          }            
          break;
        case 'labelling':
          $tmp_std_array = explode(',',$std);
          $std_part = 0;
          $tmp_std_array = explode(',',$std);
          $std_part = 0;
          $max_col1 = 0;
          $max_col2 = 0;
          $tmp_first_split = explode(';', $correct);
          $tmp_second_split = explode('|', $tmp_first_split[11]);
          foreach ($tmp_second_split as $ind_label) {
            $label_parts = explode('$', $ind_label);
            if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
              if ($label_parts[0] < 10) {
                $max_col1 = $label_parts[0];
              } else {
                $max_col2 = $label_parts[0];
              }
            }
          }
          $max_col2-=10;
          
          $max_label = max($max_col1, $max_col2);

          $tmp_height = $q_media_height;
          if ($tmp_height < ($max_label * 55)) $tmp_height = ($max_label * 55);
          $correct = str_replace('"', '&#034;', $correct);
          $correct = str_replace("'", '&#039;', $correct);
?>
    <div align="center">
    <script language="JavaScript">
      function swfLoaded<?php echo $q_no; ?>(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $q_media; ?>', '<?php echo trim($correct); ?>', '');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $q_no; ?>" width="<?php echo ($q_media_width + 250); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="<?php echo $cfg_root_path ?>/reports/label_analysis.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="#ffffff" />');
      write_string('<embed src="<?php echo $cfg_root_path ?>/reports/label_analysis.swf" quality="high" bgcolor="#ffffff" width="<?php echo ($q_media_width + 250); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $q_no; ?>" name="flash<?php echo $q_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
    </script>
    </div>
    <br />
<?php
          break;
        case 'hotspot':
          $tmp_width = ($q_media_width + 301);
          if ($tmp_width < 375) $tmp_width = 375;
          $tmp_height = $q_media_height + 30;
          ?>
              <div>
              <script language="JavaScript">
                function swfLoaded<?php echo $q_no; ?>(message) {
                  var num = message.substring(5,message.length);
                  setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $q_media; ?>', '<?php echo str_replace('&nbsp;', ' ', $correct); ?>', '', '1,1,0000000000000');
                }
                write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $q_no; ?>" width="<?php echo $tmp_width; ?>" height="<?php echo $tmp_height; ?>" align="middle">');
                write_string('<param name="allowScriptAccess" value="always" />');
                write_string('<param name="movie" value="<?php echo $cfg_root_path ?>/paper/hotspot_answer.swf" />');
                write_string('<param name="quality" value="high" />');
                write_string('<param name="bgcolor" value="white" />');
                write_string('<embed src="<?php echo $cfg_root_path ?>/paper/hotspot_answer.swf" quality="high" bgcolor="<?php echo 'white'; ?>" width="<?php echo $tmp_width; ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $q_no; ?>" name="flash<?php echo $q_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
                write_string('</object>');
              </script>
              </div>
          <?php
          
          break;
        case 'mcq':
          $i = 0;
          foreach ($options as $individual_option) {
            $i++;
            if ($correct == $i) {
              echo "<tr><td><input type=\"radio\" checked=\"checked\" /></td><td>$individual_option</td></tr>\n";
            } else {
              echo "<tr><td><input type=\"radio\" /></td><td>$individual_option</td></tr>\n";
            }
          }
          break;
        case 'true_false':
          if ($correct == 't') {
            echo "<tr><td><input type=\"radio\" checked=\"checked\" /></td><td>True</td></tr>\n";
            echo "<tr><td><input type=\"radio\" /></td><td>False</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"radio\" /></td><td>True</td></tr>\n";
            echo "<tr><td><input type=\"radio\" checked=\"checked\" /></td><td>False</td></tr>\n";
          }
          break;
        case 'mrq':
          $tmp_std_array = explode(',',$std);
          $i = 0;
          $correct_stems = 0;
          foreach ($options as $individual_option) {
            $i++;
            if ($correct_buf[$i-1] == 'y') {
              echo "<tr><td><input type=\"checkbox\" checked=\"checked\" /></td><td>$individual_option</td></tr>\n";
            } else {
              echo "<tr><td><input type=\"checkbox\" /></td><td>$individual_option</td></tr>\n";
            }
          }
          break;
        case 'rank':
          $tmp_std_array = explode(',', $std);
          $std_part = 0;
          $rank_no = 0;
          foreach ($correct_buf as $individual_correct) {
            if ($individual_correct > $rank_no and $individual_correct < 9990) $rank_no = $individual_correct;
          }
          
          $i = 0;
          foreach ($options as $individual_option) {
            $i++;
            echo "<tr><td><select><option value=\"\"></option>";
            for ($a=1; $a<=$rank_no; $a++) {
              //echo '<option value="">' . displayRank($correct_buf[$a]) . '</option>';
              if ($correct_buf[$i-1] == $a) {
                echo '<option value="" selected="selected">' . displayRank($a) . '</option>';
              } else {
                echo '<option value="">' . displayRank($a) . '</option>';
              }
            }
            echo "</select></td><td>$individual_option</td></tr>\n";
          }
          break;
        case 'textbox':
          $correct_answers = explode(';', $correct);
          foreach ($correct_answers as $single_answer) {
            $answer_count[$single_answer] = 0;
          }
          break;
      }
      if ($q_type != 'info' and $q_type != 'blank' and $q_type != 'labelling' and $q_type != 'hotspot') echo "</table></p>\n";
    } elseif ($q_type == 'matrix') {
      $matching_scenarios = explode('|', $scenario);
      $correct_answers = explode('|', $correct);
      echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin\n";
      echo '<ol type="i">';
      $i = 0;
      echo '<table cellpadding="2" cellspacing="0" border="1" class="matrix">';
      echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
      foreach ($options as $single_option) {
        echo '<td>' . $single_option . '</td>';
      }

      echo "<tr>\n";

      $row_no = 0;
      foreach ($matching_scenarios as $single_scenario) {
        if (trim($single_scenario) != '') {
          echo "<tr>\n";
          echo '<td align="right">' . chr(65 + $row_no) . '.</td><td>' . $single_scenario . '</td>';
          $answer_no = 1;
          $col_no = 1;
          foreach ($options as $single_option) {
            if ($correct_answers[$row_no] == $col_no) {
              echo '<td><div align="center"><input type="radio" name="q' . $q_no . '_' . $row_no . '" value="' . $answer_no . '" checked /></div></td>';
            } else {
              echo '<td><div align="center"><input type="radio" name="q' . $q_no . '_' . $row_no . '" value="' . $answer_no . '" /></div></td>';
            }
            $answer_no++;
            $col_no++;
          }
          echo "</tr>\n";
          $row_no++;
        }
      }    
      echo '</table>';
      echo "</ol>\n</td></tr>\n";
    } elseif ($q_type == 'extmatch') {
      $matching_scenarios = array();
      $matching_scenarios = explode('|', $scenario);
      $tmp_media_array = explode('|',$q_media);
      $tmp_media_width_array = explode('|',$q_media_width);
      $tmp_media_height_array = explode('|',$q_media_height);
      $tmp_ext_scenarios = explode('|',$scenario);
      $tmp_answers_array = explode('|',$correct_buf[0]);
      $tmp_std_array = explode(',',$std);
      $std_part = 0;
      
      $tmp_text_no = 0;
      $tmp_media_no = 0;
      foreach ($matching_scenarios as $single_scenario) {
        if (trim($single_scenario) != '') $tmp_text_no++;
      }          
      foreach ($tmp_media_array as $single_media) {
        if (trim($single_media) != '') $tmp_media_no++;
      }
      if ($tmp_text_no > $tmp_media_no) {
        $total_scenarios = $tmp_text_no;
      } else {
        $total_scenarios = $tmp_media_no;
      }
      
      echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin\n<ol type=\"A\">";
      if ($tmp_media_array[0] != '') {
        echo "<div align=\"center\">" . display_media($tmp_media_array[0],$tmp_media_width_array[0],$tmp_media_height_array[0]) . "</div>\n";
      }
      for ($i=1; $i<=$total_scenarios; $i++) {
        echo "<li>\n";
        if (isset($tmp_media_array[$i]) and $tmp_media_array[$i] != '') {
          echo "<div>" . display_media($tmp_media_array[$i],$tmp_media_width_array[$i],$tmp_media_height_array[$i]) . "</div>\n";
        }
        if ($tmp_ext_scenarios[$i-1]) echo $tmp_ext_scenarios[$i-1] . '<br />';
        $option_no = 1;
        $specific_answers = array();
        $specific_answers = explode('$', $tmp_answers_array[$i-1]);
        if (count($specific_answers) > 1) {
          echo '<select multiple="multiple" size="10">';
        } else {
          echo '<select>';
        }
        foreach ($options as $individual_option) {
          $answer_match = false;
          for ($x=0; $x<count($specific_answers); $x++) {
            if ($option_no == $specific_answers[$x]) $answer_match = true;
          }
          if ($answer_match == true) {
            echo "<option value=\"\" selected=\"selected\">$individual_option</option>\n";
          } else {
            echo "<option value=\"\">$individual_option</option>\n";
          }
          $option_no++;
        }
        echo "</select><br />&nbsp;</li>\n";
      }
      echo "</ol>\n";
    }
    echo "</td></tr>\n";
    
    // Display comments here.
    if ($q_type != 'info') echo displayComments($q_id, $comments, $q_type, $q_no);
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo ucfirst($type); ?> Comments Report</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <style type="text/css">
  body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
  h1 {margin-left:15px; font-size:18pt}
  p {margin-left:0px; margin-right:15px; margin-top:0px; padding-top:0px}
  .h {background-color:#F1F5FB; color:black}
  .figures {text-align:right}
  .q_no {text-align:right; vertical-align:top; width:50px}
  .grey {color:#808080}
  .OK {}
  .Minor {}
  .Major {}
  .breadcrumb {margin-left:10px; font-size:90%}
  .breadcrumb a:link {color:blue; text-decoration:none; cursor:pointer}
  .breadcrumb a:visited {color:blue; text-decoration:none; cursor:pointer}
  .breadcrumb a:hover {color:blue; text-decoration:underline; cursor:pointer}
  .matrix {border:1px solid #808080; border-collapse:collapse}
  .matrix td {border:1px solid #808080}
  .scr_no {margin-left:25px}
  .screenbrk {
    color:#15428B;
    font-weight:bold;
    font-size:90%;
    height:70px;
    width:100%;
    border-top: 1px solid #B5C4DF;
    background: -moz-linear-gradient(top, #E4EEFC, #FFFFFF);
    background: -webkit-linear-gradient(top, #E4EEFC, #FFFFFF);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#E4EEFC', endColorstr='#FFFFFF');
  }
  .reviewbar {
    color:black;
    width:100%;
    border-top: 1px solid #6593CF;
    border-bottom: 1px solid #6593CF;
    background: -moz-linear-gradient(top, #FFFFFF, #C5DEFF);
    background: -webkit-linear-gradient(top, #FFFFFF, #C5DEFF);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FFFFFF', endColorstr='#C5DEFF');
  }
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script src="../js/flash_include.js" type="text/javascript"></script>
  <script src="../js/ie_fix.js" type="text/javascript"></script>
  <script type="text/javascript">
    function getScrollXY() {
      var scrOfX = 0, scrOfY = 0;
      if( typeof( window.pageYOffset ) == 'number' ) {
        //Netscape compliant
        scrOfY = window.pageYOffset;
        scrOfX = window.pageXOffset;
      } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        //DOM compliant
        scrOfY = document.body.scrollTop;
        scrOfX = document.body.scrollLeft;
      } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        //IE6 standards compliant mode
        scrOfY = document.documentElement.scrollTop;
        scrOfX = document.documentElement.scrollLeft;
      }
      document.getElementById('scrOfY').value = scrOfY;
    }

    function editQuestion(qtype,qid,qno) {
      location.href='../question/edit/index.php?type=' + qtype + '&q_id=' + qid + '&qNo=' + qno + '&paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>&calling=<?php echo $type; ?>_comments&scrOfY=' + document.getElementById('scrOfY').value + '&tab=comments';
    }

    function showHideSerious() {
      if (document.styleSheets[0].rules) {
        document.styleSheets[0].rules.item(7).style.display = (document.styleSheets[0].rules.item(7).style.display == 'none') ? 'block' : 'none';
      } else {
        document.styleSheets[0].cssRules[7].style.display = (document.styleSheets[0].cssRules[7].style.display == 'none') ? 'table-row' : 'none';
      }
    }
  </script>
</head>

<body onscroll="getScrollXY()"<?php
if (isset($_GET['scrOfY'])) {
  if ($_GET['scrOfY'] > 0) echo ' onload="window.scrollTo(0,' . $_GET['scrOfY'] . ')"';
}
?>>
<form name="theform">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?php
  // Get some paper properties
  $result = $mysqli->prepare("SELECT paper_type, paper_title, marking, pass_mark, externals, internal_reviewers FROM properties WHERE property_id=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_type, $paper, $marking, $pass_mark, $external_reviewers, $internal_reviewers);
  $result->fetch();
  $result->close();
  if($type == 'external') {
    $reviewers = $external_reviewers;
  } else {
    $reviewers = $internal_reviewers;
  }
  $comments_array = array();
  if ($reviewers != '') {
    $reviewers_split = explode(',',$reviewers);
    $incomplete_array = array();
    foreach ($reviewers_split as $incomplete_individual) {
      $incomplete_array[$incomplete_individual] = '1';
    }
  
    // Capture reviewer comments data first.
    $result = $mysqli->prepare("SELECT title, initials, surname, q_id, comment, category, DATE_FORMAT(reviewed,'%d/%m/%Y %T') AS reviewed, reviewer, action, response FROM (review_comments, users) WHERE review_comments.reviewer=users.id AND review_type=? AND q_paper=?");
    $result->bind_param('si', $type,$paperID);
    $result->execute();
    $result->bind_result($title, $initials, $surname, $tmp_q_id, $comment, $category, $reviewed, $tmp_reviewer, $action, $response);
    while ($row = $result->fetch()) {
      if (isset($incomplete_array[$tmp_reviewer])) {
        unset($incomplete_array[$tmp_reviewer]);
      }
    
      $comments_array[$tmp_q_id][$tmp_reviewer]['name'] = $title . ' ' . $initials . ' ' . $surname;
      $comments_array[$tmp_q_id][$tmp_reviewer]['reviewed'] = $reviewed;
      $comments_array[$tmp_q_id][$tmp_reviewer]['comment'] = $comment;
      $comments_array[$tmp_q_id][$tmp_reviewer]['category'] = $category;
      $comments_array[$tmp_q_id][$tmp_reviewer]['action'] = $action;
      $comments_array[$tmp_q_id][$tmp_reviewer]['response'] = $response;
    }
    $result->close();
  
    $incomplete_names = array();
    $reviewers = explode(',',$reviewers);
    foreach ($incomplete_array as $reviwer=>$flag) {
      $result = $mysqli->prepare("SELECT title, initials, surname FROM users WHERE users.id=? LIMIT 1");
      $result->bind_param('i', $reviwer);
      $result->execute();
      $result->store_result();
      $result->bind_result($tmp_title, $tmp_initials, $tmp_surname);
      $result->fetch();
      $result->close();
      
      $incomplete_names[] = $tmp_title . ' ' . $tmp_initials . ' ' . $tmp_surname;
    }
  }
  
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

  // Capture the paper makeup.
  $display_header = true;
  $question_no = 0;
  $old_q_id = 0;
  $old_screen = 1;
  $options_buffer = array();
  $correct_buffer = array();

  $result = $mysqli->prepare("SELECT paper_title, labelcolor, themecolor, screen, q_id, q_type, theme, scenario, leadin, option_text, display_method, score_method, q_media, q_media_width, q_media_height, correct, std FROM (properties, papers, questions, options) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND questions.q_id=options.o_id AND papers.paper=? ORDER BY screen, display_pos, id_num");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title, $labelcolor, $themecolor, $screen, $q_id, $q_type, $theme, $scenario, $leadin, $option_text, $display_method, $score_method, $q_media, $q_media_width, $q_media_height, $correct, $std);
  while ($result->fetch()) {
    if ($display_header == true) {
      echo '<tr><td class="h"><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
      if ($folder != '') {
        echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
      } elseif (isset($_GET['module']) and $_GET['module'] != '') {
        echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
      }
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';

      echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">" . $string[$type . 'report'] . "</span></td><td class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
      echo "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"2\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n</table>\n";
      if ($reviewers == '') {
        echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"width:46px; height:32px; padding-left:6px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/warning_user_icon.gif\" style=\"padding-top:1px\" width=\"32\" height=\"30\" alt=\"!\" />&nbsp;&nbsp;</td><td style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\">" . $string['noreviewers'] . "</td></tr></table>\n</body>\n</html>\n";
        exit;
      }
      echo '<br /><table cellpadding="0" cellspacing="0" border="0" width="100%">';
      $display_header = false;
    }
    if ($question_no == 0) {
      $old_labelcolor = $labelcolor;
      $old_themecolor = $themecolor;
    }
    if ($old_q_id != $q_id and $old_q_id > 0) {   // New question.
      $question_no++;
      if ($old_q_type == 'info') $question_no--;
      displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $comments_array, $correct_buffer, $old_display_method, $old_score_method, $old_labelcolor, $old_themecolor, $old_std);
      $options_buffer = array();
      $correct_buffer = array();
      if ($old_screen != $screen) {
        echo '<tr><td colspan="2">';
        echo '<div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div>';
        echo '</td></tr>';
      }
    }
    if ($q_type == 'labelling') {
      $tmp_first_split = explode(';', $correct);
      $tmp_second_split = explode('$', $tmp_first_split[11]);
      for ($label_no = 4; $label_no <= 43; $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|') {
          $options_buffer[] = trim(substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'))) . '|' . $tmp_second_split[$label_no-2] . '|' . ($tmp_second_split[$label_no-1] - 25);
          if ($tmp_second_split[$label_no-2] > 150) {
            $correct_buffer[] = $tmp_second_split[$label_no-2] . 'x' . ($tmp_second_split[$label_no-1] - 25);
          }
        }
      }
    } elseif ($q_type == 'blank') {
      $blank_details = explode('[blank',$option_text);
      $no_answers = count($blank_details) - 1;
      for ($i=1; $i<=$no_answers; $i++) {
        $blank_details[$i] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$blank_details[$i]);
        $blank_details[$i] = preg_replace("| size=\"([0-9]{1,3})\"|","",$blank_details[$i]);

        $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
        $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
        $answer_list = explode(',',$blank_details[$i]);
        $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
        if ($score_method == 'textboxes') {
          foreach ($answer_list as $individual_answer) {
            $correct_buffer[] = html_entity_decode(trim($individual_answer));
          }
        } else {
          $correct_buffer[] = html_entity_decode(trim($answer_list[0]));
        }
      }
      $options_buffer[] = $option_text;
    } else {
      $options_buffer[] = $option_text;
      $correct_buffer[] = $correct;
    }
    $old_q_id = $q_id;
    $old_theme = $theme;
    $old_scenario = $scenario;
    $old_leadin = $leadin;
    $old_q_type = $q_type;
    $old_q_media = $q_media;
    $old_q_media_width = $q_media_width;
    $old_q_media_height = $q_media_height;
    $old_correct = $correct;
    $old_display_method = $display_method;
    $old_score_method = $score_method;
    $old_std = $std;
    $old_screen = $screen;
  }
  $result->close();
  $question_no++;
  if ($old_q_type == 'info') $question_no--;
  displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $comments_array, $correct_buffer, $old_display_method, $old_score_method, $old_labelcolor, $old_themecolor, $old_std);
  $mysqli->close();
?>
</table>
<input type="hidden" name="scrOfY" id="scrOfY" value="0" />
</form>
</body>
</html>
