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
require_once '../include/survey_quantitative.inc.php';
require_once '../include/errors.php';

$paperID   = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate   = check_var('enddate', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

header('Pragma: public');
header('Content-disposition: attachment; filename=report.xml');
header('Content-type: text/xml');

function displayQuestion($q_id, $theme, $scenario, $leadin, $q_type, $correct, $q_media, $q_media_width, $q_media_height, $options, $log, $correct_buf, $screen, $question_number, $candidates) {
  global $old_likert_scale, $old_display_method, $table_on;

  // Remove spaces
  $theme = str_replace('&nbsp;',' ',$theme);
  $scenario = str_replace('&nbsp;',' ',$scenario);
  $leadin = str_replace('&nbsp;',' ',$leadin);
  $old_likert_scale = str_replace('&nbsp;',' ',$old_likert_scale);

  // Remove nasty non-utf8 chars
  $theme = StringUtils::wordToUtf8(strip_tags($theme));
  $scenario = StringUtils::wordToUtf8(strip_tags($scenario));
  $leadin = StringUtils::wordToUtf8(strip_tags($leadin));
	
  $theme = str_replace('&amp;amp;','&amp;',$theme);
  $scenario = str_replace('&amp;amp;','&amp;',$scenario);
  $leadin = str_replace('&amp;amp;','&amp;',$leadin);
  $old_likert_scale = trim(strip_tags(str_replace('&amp;amp;','&amp;',$old_likert_scale)));

  if ($theme != '') {
    if ($table_on == 1) echo '</w:tbl>';
    echo '<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>' . $theme . '</w:t></w:r></w:p><w:p/>';
    $table_on = 0;
  }
  if ($q_type != 'extmatch') {
    switch ($q_type) {
      case 'dichotomous':
        if ($table_on == 1) echo '</w:tbl>';
        echo "<w:p><w:r><w:t>$question_number. $leadin</w:t></w:r></w:p><w:p/>";
        if ($old_display_method == 'YN_Positive' or $old_display_method == 'YN_NegativeAbstain') {
          echo '<w:p><w:pPr><w:tabs><w:tab w:val="center" w:pos="650"/><w:tab w:val="center" w:pos="1700"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:rPr><w:b/></w:rPr><w:t>Yes</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:rPr><w:b/></w:rPr><w:t>No</w:t></w:r></w:p>';
        } else {
          echo '<w:p><w:pPr><w:tabs><w:tab w:val="center" w:pos="600"/><w:tab w:val="center" w:pos="1600"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:rPr><w:b/></w:rPr><w:t>True</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:rPr><w:b/></w:rPr><w:t>False</w:t></w:r></w:p>';
        }
        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          if ($log[$screen][$q_id][$i]['u'] == '') $log[$screen][$q_id][$i]['u'] = 0;
          if ($log[$screen][$q_id][$i]['t'] == '') $log[$screen][$q_id][$i]['t'] = 0;
          if ($log[$screen][$q_id][$i]['f'] == '') $log[$screen][$q_id][$i]['f'] = 0;
          echo '<w:p wsp:rsidR="00E97566" wsp:rsidRDefault="00E97566" wsp:rsidP="00E97566"/><w:p wsp:rsidR="00E97566" wsp:rsidRDefault="00E97566" wsp:rsidP="00E97566"><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="500"/><w:tab w:val="left" w:pos="550"/><w:tab w:val="decimal" w:pos="1450"/><w:tab w:val="left" w:pos="1500"/><w:tab w:val="left" w:pos="2400"/></w:tabs><w:ind w:left="2340" w:hanging="2340"/></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>' . $log[$screen][$q_id][$i]['t'] . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round(($log[$screen][$q_id][$i]['t']/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>' . $log[$screen][$q_id][$i]['f'] . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round(($log[$screen][$q_id][$i]['f']/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
        }
        echo '<w:p/>';
        $table_on = 0;
        break;
      case 'mcq':
        if ($table_on == 1) echo '</w:tbl>';
        echo "<w:p><w:r><w:t>$question_number. $leadin</w:t></w:r></w:p><w:p/>";
        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          if (!isset($log[$screen][$q_id][1][$i]) or $log[$screen][$q_id][1][$i] == '') {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>0</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(0%)</w:t></w:r><w:r><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          } else {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>' . $log[$screen][$q_id][1][$i] . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round(($log[$screen][$q_id][1][$i]/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          }
        }
        if ($old_display_method == 'vertical_other') {
          echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>' . count($log[$screen][$q_id][1]['other']) . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round((count($log[$screen][$q_id][1]['other'])/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>Other:</w:t></w:r></w:p>';
          foreach ($log[$screen][$q_id][1]['other'] as $other_text) {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="2268"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="900" wx:tlc="none" wx:cTlc="19"/></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/></w:r><w:r><w:tab wx:wTab="1185" wx:tlc="none" wx:cTlc="25"/><w:t>' . StringUtils::wordToUtf8($other_text) . '</w:t></w:r></w:p>';
          }
        }
        if (!isset($log[$screen][$q_id][1]['u'])) {
          echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:rPr><w:color w:val="999999"/></w:rPr><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>0</w:t></w:r><w:r><w:rPr><w:color w:val="999999"/></w:rPr><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(0%)</w:t></w:r><w:r><w:rPr><w:color w:val="999999"/></w:rPr><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>(unanswered)</w:t></w:r></w:p>';
        } else {
          $unanswered = $log[$screen][$q_id][1]['u'];
          echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:rPr><w:color w:val="999999"/></w:rPr><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>' . $log[$screen][$q_id][1]['u'] . '</w:t></w:r><w:r><w:rPr><w:color w:val="999999"/></w:rPr><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round(($log[$screen][$q_id][1]['u']/$candidates)*100) . '%)</w:t></w:r><w:r><w:rPr><w:color w:val="999999"/></w:rPr><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>(unanswered)</w:t></w:r></w:p>';
        }
        echo '<w:p/>';
        $table_on = 0;
        break;
      case 'likert':
        $unanswered = 0;
        $old_size = substr_count($old_likert_scale,'|');
        $current_properties = explode('|',$old_display_method);
        $new_size = substr_count($old_display_method,'|');
        $na = $current_properties[$new_size];
        if ($old_likert_scale != $old_display_method or $table_on == 0) {
          if ($table_on == 1) echo '</w:tbl>';
          echo '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/><w:tblW w:w="0" w:type="auto"/><w:tblLook w:val="01E0"/></w:tblPr><w:tblGrid><w:gridCol w:w="470"/><w:gridCol w:w="4350"/><w:gridCol w:w="500"/><w:gridCol w:w="780"/><w:gridCol w:w="780"/><w:gridCol w:w="780"/><w:gridCol w:w="1290"/><w:gridCol w:w="780"/></w:tblGrid>';
          echo '<w:tr><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>No</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Question</w:t></w:r></w:p></w:tc>';
          if ($na == 'true') echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>N/A</w:t></w:r></w:p></w:tc>';
          for ($point=0; $point<$new_size; $point++) {
            echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>' . str_replace(array('<br>', '<br />'), '</w:t></w:r><w:r><w:br/></w:r><w:r><w:rPr><w:b/></w:rPr><w:t>', strip_tags($current_properties[$point],'<br>,<br />')) . '</w:t></w:r></w:p></w:tc>';
          }
          echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Unanswered</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Mean</w:t></w:r></w:p></w:tc></w:tr>';
          $table_on = 1;
        }
        echo '<w:tr><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $question_number . '.</w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $leadin . '</w:t></w:r></w:p></w:tc>';
        $i = 0;
        $sub_total = 0;
        foreach ($options as $individual_option) {
          $i++;
          if ($i > 1 or $na == 'true') {
            if (!isset($log[$screen][$q_id][1][$individual_option])) {
              echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>0</w:t></w:r></w:p></w:tc>';
            } else {
              echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $log[$screen][$q_id][1][$individual_option] . ' (' . round(($log[$screen][$q_id][1][$individual_option]/$candidates)*100) . '%)</w:t></w:r></w:p></w:tc>';
            }
            if ($individual_option >= 1 and $individual_option <= 10) {
              if (isset($log[$screen][$q_id][1][$individual_option])) {
                $sub_total += $individual_option * $log[$screen][$q_id][1][$individual_option];
              }
            }
          }
        }
        if (isset($log[$screen][$q_id][1]['n/a'])) $unanswered = $log[$screen][$q_id][1]['n/a'];
        if (!isset($log[$screen][$q_id][1]['u'])) {
          echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>0</w:t></w:r></w:p></w:tc>';
        } else {
          $unanswered += $log[$screen][$q_id][1]['u'];
          echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $log[$screen][$q_id][1]['u'] . ' (' . round(($log[$screen][$q_id][1]['u']/$candidates)*100) . '%)</w:t></w:r></w:p></w:tc>';
        }
        if (($candidates - $unanswered) == 0) {
          echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>0</w:t></w:r></w:p></w:tc></w:tr>';
        } else {
          echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . number_format($sub_total/($candidates-$unanswered),1) . '</w:t></w:r></w:p></w:tc></w:tr>';
        }
        $old_likert_scale = $old_display_method;
        break;
      case 'mrq':
        if ($table_on == 1) echo '</w:tbl>';
        echo "<w:p><w:r><w:t>$question_number. $leadin</w:t></w:r></w:p><w:p/>";
        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          if ($candidates == 0) {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>0</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(0%)</w:t></w:r><w:r><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          } else {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>' . $log[$screen][$q_id][$i]['y'] . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round(($log[$screen][$q_id][$i]['y']/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          }
        }
        if ($old_display_method == 'other') {
          echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1800"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="795" wx:tlc="none" wx:cTlc="17"/><w:t>' . count($log[$screen][$q_id][1]['other']) . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round((count($log[$screen][$q_id][1]['other'])/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="720" wx:tlc="none" wx:cTlc="15"/></w:r><w:r><w:t>Other:</w:t></w:r></w:p>';
          foreach ($log[$screen][$q_id][1]['other'] as $other_text) {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="2268"/></w:tabs></w:pPr><w:r><w:tab wx:wTab="900" wx:tlc="none" wx:cTlc="19"/></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/></w:r><w:r><w:tab wx:wTab="1185" wx:tlc="none" wx:cTlc="25"/><w:t>' . StringUtils::wordToUtf8($other_text) . '</w:t></w:r></w:p>';
          }
        }
        $table_on = 0;
        break;
      case 'rank':
        if ($table_on == 1) echo '</w:tbl>';
        echo "<w:p><w:r><w:t>$question_number. $leadin</w:t></w:r></w:p><w:p/>";
        $rank_no = 0;
        foreach ($correct_buf as $individual_correct) {
          if ($individual_correct > $rank_no and $individual_correct < 9990) $rank_no = $individual_correct;
        }
        $i = 0;
        foreach ($options as $individual_option) {
          echo "<w:p><w:r><w:t>" . StringUtils::wordToUtf8($individual_option) . "</w:t></w:r></w:p><w:p/>";
          echo '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/><w:tblW w:w="0" w:type="auto"/><w:tblLook w:val="01E0"/></w:tblPr><w:tblGrid><w:gridCol w:w="1500"/><w:gridCol w:w="1500"/><w:gridCol w:w="1500"/></w:tblGrid>';
          for ($rank_position=1; $rank_position<=$rank_no; $rank_position++) {
            if (!isset($log[$screen][$q_id][$i][$rank_position])) $log[$screen][$q_id][$i][$rank_position] = 0;
            echo '<w:tr><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $log[$screen][$q_id][$i][$rank_position] . '</w:t></w:r></w:p></w:tc>';
            echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>(' . number_format(($log[$screen][$q_id][$i][$rank_position]/$candidates)*100,0) . '%)</w:t></w:r></w:p></w:tc>';
            echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $rank_position;
            if ($rank_position == 1) {
              echo 'st';
            } elseif ($rank_position == 2) {
              echo 'nd';
            } elseif ($rank_position == 3) {
              echo 'rd';
            } else {
              echo 'th';
            }
            echo '</w:t></w:r></w:p></w:tc></w:tr>';
          }
          echo '</w:tbl>';
          $i++;
        }
        $table_on = 0;
        break;
      case 'matrix':
        if ($table_on == 1) echo '</w:tbl>';
        echo "<w:p><w:r><w:t>$question_number. $leadin</w:t></w:r></w:p><w:p/>";
        // Define the table grid
        echo '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/><w:tblW w:w="0" w:type="auto"/><w:tblLook w:val="01E0"/></w:tblPr><w:tblGrid><w:gridCol w:w="2500"/>';
        foreach ($options as $option) {
          echo '<w:gridCol w:w="1500"/>';
        }
        echo '</w:tblGrid>';

        // Write out the header row of the table
        echo '<w:tr><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:r><w:t></w:t></w:r></w:p></w:tc>';
        foreach ($options as $option) {
          echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="E0E0E0"/></w:tcPr><w:p><w:r><w:t>' . $option . '</w:t></w:r></w:p></w:tc>';
        }
        echo '</w:tr>';

        // Write out the contents of the table
        $row_data = explode('|',$scenario);
        $option_no = count($options);
        $row_no = 0;
        foreach ($row_data as $row) {
          echo '<w:tr><w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $row . '</w:t></w:r></w:p></w:tc>';
          for ($i=1; $i<=$option_no; $i++) {
            if (isset($log[$screen][$q_id][$row_no][$i])) {
              echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>' . $log[$screen][$q_id][$row_no][$i] . '(' . number_format(($log[$screen][$q_id][$row_no][$i]/$candidates)*100,0) . '%)</w:t></w:r></w:p></w:tc>';
            } else {
              echo '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr><w:p><w:r><w:t>0 (0%)</w:t></w:r></w:p></w:tc>';
            }
          }
          echo '</w:tr>';
          $row_no++;
        }
        echo '</w:tbl>';
        break;
    }
  } else {
    $tmp_media_array = explode('|',$q_media);
    $tmp_media_width_array = explode('|',$q_media_width);
    $tmp_media_height_array = explode('|',$q_media_height);
    $tmp_ext_scenarios = explode('|',$scenario);
    $tmp_answers_array = explode('|',$correct_buf[0]);
    echo "<w:p><w:r><w:t>$question_number. $leadin</w:t></w:r></w:p><w:p/>";
    for ($i=1; $i<=(substr_count($scenario,'|')+1); $i++) {
      if ($tmp_ext_scenarios[$i-1])  echo "<w:p><w:r><w:t>" . $tmp_ext_scenarios[$i-1] . "</w:t></w:r></w:p><w:p/>";
      $option_no = 1;
      foreach ($options as $individual_option) {
        if ($tmp_answers_array[$i-1] == $option_no) {
          if ($log[$screen][$q_id][$i][$option_no] == '') {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1843"/></w:tabs><w:ind w:left="1843" w:hanging="1843"/></w:pPr><w:r><w:tab wx:wTab="585" wx:tlc="none" wx:cTlc="12"/><w:t>0</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(0%)</w:t></w:r><w:r><w:tab wx:wTab="270" wx:tlc="none" wx:cTlc="5"/><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          } else {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1843"/></w:tabs><w:ind w:left="1843" w:hanging="1843"/></w:pPr><w:r><w:tab wx:wTab="585" wx:tlc="none" wx:cTlc="12"/><w:t>' . $log[$screen][$q_id][$i][$option_no] . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round(($log[$screen][$q_id][$i][$option_no]/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="270" wx:tlc="none" wx:cTlc="5"/><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          }
        } else {
          if ($log[$screen][$q_id][$i][$option_no] == '') {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1843"/></w:tabs><w:ind w:left="1843" w:hanging="1843"/></w:pPr><w:r><w:tab wx:wTab="585" wx:tlc="none" wx:cTlc="12"/><w:t>0</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(0%)</w:t></w:r><w:r><w:tab wx:wTab="270" wx:tlc="none" wx:cTlc="5"/><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          } else {
            echo '<w:p><w:pPr><w:tabs><w:tab w:val="decimal" w:pos="900"/><w:tab w:val="left" w:pos="1080"/><w:tab w:val="left" w:pos="1843"/></w:tabs><w:ind w:left="1843" w:hanging="1843"/></w:pPr><w:r><w:tab wx:wTab="585" wx:tlc="none" wx:cTlc="12"/><w:t>' . $log[$screen][$q_id][$i][$option_no] . '</w:t></w:r><w:r><w:tab wx:wTab="180" wx:tlc="none" wx:cTlc="3"/><w:t>(' . round(($log[$screen][$q_id][$i][$option_no]/$candidates)*100) . '%)</w:t></w:r><w:r><w:tab wx:wTab="270" wx:tlc="none" wx:cTlc="5"/><w:t>' . StringUtils::wordToUtf8($individual_option) . '</w:t></w:r></w:p>';
          }
        }
        $option_no++;
      }
    }
  }
}

$exclude = '';
if ($_GET['complete'] == 1) {
  $result = $mysqli->prepare("SELECT userID, COUNT(id) AS answer_no FROM log3 WHERE q_paper = ? AND started >= ? AND started <= ? GROUP BY userID");
  $result->bind_param('iss', $paperID, $startdate, $enddate);
  $result->execute();
  $result->bind_result($tmp_username, $answer_no);
  while ($result->fetch()) {
    if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
      $exclude .= ' AND log3.userID != "' . $tmp_username . '"';
    }
  }
  $result->close();
}

$paper = str_replace('&', '&amp;', $propertyObj->get_paper_title());
$number_of_questions = $propertyObj->get_question_no();

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?mso-application progid="Word.Document"?>
<w:wordDocument xmlns:w="http://schemas.microsoft.com/office/word/2003/wordml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:sl="http://schemas.microsoft.com/schemaLibrary/2003/core" xmlns:aml="http://schemas.microsoft.com/aml/2001/core" xmlns:wx="http://schemas.microsoft.com/office/word/2003/auxHint" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:dt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882" xmlns:st1="urn:schemas-microsoft-com:office:smarttags" xmlns:wsp="http://schemas.microsoft.com/office/word/2003/wordml/sp2" w:macrosPresent="no" w:embeddedObjPresent="no" w:ocxPresent="no" xml:space="preserve"><o:SmartTagType o:namespaceuri="urn:schemas-microsoft-com:office:smarttags" o:name="City"/><o:SmartTagType o:namespaceuri="urn:schemas-microsoft-com:office:smarttags" o:name="place"/><o:DocumentProperties><o:Title>';
echo $paper;
$tmp_start = substr($startdate, 6, 2) . '/' . substr($startdate, 4, 2) . '/' . substr($startdate, 0, 4) . ' ' . substr($startdate, 8, 2) . ':' . substr($startdate, 10, 2);
$tmp_end = substr($enddate, 6, 2) . '/' . substr($enddate, 4, 2) . '/' . substr($enddate, 0, 4) . ' ' . substr($enddate, 8, 2) . ':' . substr($enddate, 10, 2);
echo '</o:Title><o:Author>Rogo ' . $configObject->get('rogo_version') . '</o:Author><o:Description>Quantitative report for survey taken between ' . $tmp_start . ' and ' . $tmp_end .'.</o:Description><o:LastAuthor>Rogo ' . $configObject->get('rogo_version') . '</o:LastAuthor><o:Revision>1</o:Revision><o:TotalTime>0</o:TotalTime><o:Created>';
echo date('Y-m-d', time()) . 'T' . date('H:i:s') . 'Z';
echo '</o:Created><o:LastSaved>';
echo date('Y-m-d', time()) . 'T' . date('H:i:s') . 'Z';
echo '</o:LastSaved><o:Pages>1</o:Pages><o:Company>' . $configObject->get('cfg_company') . '</o:Company>';
echo '</o:DocumentProperties><w:fonts><w:defaultFonts w:ascii="Times New Roman" w:fareast="Times New Roman" w:h-ansi="Times New Roman" w:cs="Times New Roman"/></w:fonts><w:styles><w:versionOfBuiltInStylenames w:val="4"/><w:latentStyles w:defLockedState="off" w:latentStyleCount="156"/><w:style w:type="paragraph" w:default="on" w:styleId="Normal"><w:name w:val="Normal"/><w:rsid w:val="000322AA"/><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="24"/><w:lang w:val="EN-GB" w:fareast="EN-GB" w:bidi="AR-SA"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><wx:uiName wx:val="Heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:rsid w:val="000322AA"/><w:pPr><w:pStyle w:val="Heading1"/><w:keepNext/><w:spacing w:before="240" w:after="60"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:rFonts w:cs="Arial"/><wx:font wx:val="Arial"/><w:b/><w:b-cs/><w:kern w:val="32"/><w:sz w:val="32"/><w:sz-cs w:val="32"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><wx:uiName wx:val="Heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:rsid w:val="000322AA"/><w:pPr><w:pStyle w:val="Heading2"/><w:keepNext/><w:spacing w:before="240" w:after="60"/><w:outlineLvl w:val="1"/></w:pPr><w:rPr><w:rFonts w:cs="Arial"/><wx:font wx:val="Arial"/><w:b/><w:b-cs/><w:sz w:val="24"/></w:rPr></w:style><w:style w:type="character" w:default="on" w:styleId="DefaultParagraphFont"><w:name w:val="Default Paragraph Font"/><w:semiHidden/></w:style><w:style w:type="table" w:default="on" w:styleId="TableNormal"><w:name w:val="Normal Table"/><wx:uiName wx:val="Table Normal"/><w:semiHidden/><w:rPr><wx:font wx:val="Times New Roman"/></w:rPr><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style><w:style w:type="list" w:default="on" w:styleId="NoList"><w:name w:val="No List"/><w:semiHidden/></w:style><w:style w:type="table" w:styleId="TableGrid"><w:name w:val="Table Grid"/><w:basedOn w:val="TableNormal"/><w:rsid w:val="000322AA"/><w:rPr><wx:font wx:val="Times New Roman"/></w:rPr><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:insideH w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/><w:insideV w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="auto"/></w:tblBorders><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style></w:styles><w:docPr><w:view w:val="print"/><w:zoom w:percent="100"/><w:displayBackgroundShape/><w:doNotEmbedSystemFonts/><w:proofState w:spelling="clean" w:grammar="clean"/><w:attachedTemplate w:val=""/><w:defaultTabStop w:val="720"/><w:characterSpacingControl w:val="DontCompress"/><w:optimizeForBrowser/><w:allowPNG/><w:targetScreenSz w:val="1024x768"/><w:validateAgainstSchema/><w:saveInvalidXML w:val="off"/><w:ignoreMixedContent w:val="off"/><w:alwaysShowPlaceholderText w:val="off"/><w:footnotePr><w:footnote w:type="separator"><w:p><w:r><w:separator/></w:r></w:p></w:footnote><w:footnote w:type="continuation-separator"><w:p><w:r><w:continuationSeparator/></w:r></w:p></w:footnote></w:footnotePr><w:endnotePr><w:endnote w:type="separator"><w:p><w:r><w:separator/></w:r></w:p></w:endnote><w:endnote w:type="continuation-separator"><w:p><w:r><w:continuationSeparator/></w:r></w:p></w:endnote></w:endnotePr><w:compat><w:breakWrappedTables/><w:snapToGridInCell/><w:wrapTextWithPunct/><w:useAsianBreakRules/><w:useWord2002TableStyleRules/></w:compat><wsp:rsids><wsp:rsidRoot wsp:val="00E97566"/><wsp:rsid wsp:val="00634024"/><wsp:rsid wsp:val="006F2D72"/><wsp:rsid wsp:val="00E97566"/></wsp:rsids></w:docPr>';

// Document body text here.
echo '<w:body><wx:sect><wx:sub-section>';
echo '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>' . StringUtils::wordToUtf8($paper) . '</w:t></w:r></w:p>';

$log_array = array();
$hits = get_quantitative_log_data($paperID, $_GET['repcourse'], $startdate, $enddate, $exclude, $log_array, $mysqli);

$table_on = 0;

if ($hits > 0) {
  // Capture the paper makeup.
  $question_no = 1;
  $display_respondents = 1;
  $old_q_id = 0;
  $respondents = 0;
  $old_likert_scale = '';
  $old_screen = 0;
  $options_buffer = array();
  $correct_buffer = array();

  $result = $mysqli->prepare("SELECT screen, q_id, q_type, theme, scenario, leadin, option_text, display_method, q_media, q_media_width, q_media_height, correct FROM papers, questions, options WHERE papers.question = questions.q_id AND questions.q_id = options.o_id AND papers.paper = ? ORDER BY screen, display_pos, id_num");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($screen, $q_id, $q_type, $theme, $scenario, $leadin, $option_text, $display_method, $q_media, $q_media_width, $q_media_height, $correct);
  while ($result->fetch()) {
    $theme = str_replace('&nbsp;',' ',$theme);
    $scenario = str_replace('&nbsp;',' ',$scenario);
    $leadin = str_replace('&nbsp;',' ',$leadin);
    $option_text = str_replace('&nbsp;',' ',$option_text);

    // Replace & characters.
    $theme = str_replace('&','&amp;',$theme);
    $scenario = str_replace('&','&amp;',$scenario);
    $leadin = str_replace('&','&amp;',$leadin);
    $option_text = strip_tags(str_replace('&','&amp;',$option_text));

    if ($old_q_id != $q_id and $old_q_id > 0) {   // New question.
      if ($old_screen < $screen) {
        if ($table_on == 1) {
          echo '</w:tbl><w:p/>';
          $table_on = 0;
        }
        echo '<w:br w:type="page"/>';
      }
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
        if (isset($log_array[$old_screen][$old_q_id][1]['u'])) $respondents += $log_array[$old_screen][$old_q_id][1]['u'];
        if (isset($log_array[$old_screen][$old_q_id][1]['other'])) $respondents += count($log_array[$old_screen][$old_q_id][1]['other']);
        echo "<w:p><w:r><w:t>($respondents Respondents)</w:t></w:r></w:p>";
        $display_respondents = 0;
      }
      if ($old_q_type != 'info') {
        displayQuestion($old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $log_array, $correct_buffer, $old_screen, $question_no, $respondents);
        $question_no++;
      }
      if ($old_screen < $screen) {
        $display_respondents = 1;
        if ($screen > 1) {
          if ($table_on == 1) {
            echo '</w:tbl><w:p/>';
            $table_on = 0;
          }
        }
      }
      $options_buffer = array();
      $correct_buffer = array();
    }
    if ($q_type == 'labelling') {
      $tmp_first_split = explode(';', $correct);
      $tmp_second_split = explode('$', $tmp_first_split[8]);
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
    $i = 1;
    foreach ($options_buffer as $individual_option) {
      $respondents += $log_array[$old_screen][$old_q_id][1][$i];
      $i++;
    }
    $respondents += $log_array[$old_screen][$old_q_id][1]['n/a'];
    $respondents += $log_array[$old_screen][$old_q_id][1]['t'];
    $respondents += $log_array[$old_screen][$old_q_id][1]['f'];
    $respondents += $log_array[$old_screen][$old_q_id][1]['u'];
    $respondents += count($log_array[$old_screen][$old_q_id][1]['other']);
    if ($old_screen == 1) {
      echo "<w:p><w:r><w:t>($respondents Respondents)</w:t></w:r></w:p>";
    } else {
      echo "<w:p><w:r><w:br w:type=\"page\"/><w:t>($respondents Respondents)</w:t></w:r></w:p>";
    }
  }

  displayQuestion($old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $log_array, $correct_buffer, $old_screen, $question_no, $respondents);
}

if ($table_on == 1) echo '</w:tbl>';
echo '<w:sectPr><w:hdr w:type="odd"><w:p><w:pPr><w:pStyle w:val="Header"/><w:tabs><w:tab w:val="clear" w:pos="8306"/><w:tab w:val="right" w:pos="9000"/></w:tabs></w:pPr><w:r><w:t>' . $paper . '</w:t></w:r><w:r><w:tab wx:wTab="1560" wx:tlc="none" wx:cTlc="34"/><w:t>' . date("d/m/Y") . '</w:t></w:r></w:p></w:hdr><w:ftr w:type="odd"><w:p><w:pPr><w:pStyle w:val="Footer"/><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:rStyle w:val="PageNumber"/></w:rPr><w:t>- </w:t></w:r><w:r><w:rPr><w:rStyle w:val="PageNumber"/></w:rPr><w:fldChar w:fldCharType="begin"/></w:r><w:r><w:rPr><w:rStyle w:val="PageNumber"/></w:rPr><w:instrText> PAGE </w:instrText></w:r><w:r><w:rPr><w:rStyle w:val="PageNumber"/></w:rPr><w:fldChar w:fldCharType="separate"/></w:r><w:r><w:rPr><w:rStyle w:val="PageNumber"/><w:noProof/></w:rPr><w:t>1</w:t></w:r><w:r><w:rPr><w:rStyle w:val="PageNumber"/></w:rPr><w:fldChar w:fldCharType="end"/></w:r><w:r><w:rPr><w:rStyle w:val="PageNumber"/></w:rPr><w:t> -</w:t></w:r></w:p></w:ftr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1418" w:bottom="1134" w:left="1418" w:header="709" w:footer="709" w:gutter="0"/><w:cols w:space="708"/><w:docGrid w:line-pitch="360"/></w:sectPr></wx:sub-section></wx:sect></w:body></w:wordDocument>';
$mysqli->close();
?>
