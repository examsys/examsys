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
* Class total report in Excel 2003 XML format.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/class_totals.inc';

  header("Content-type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=" . $paper . ".xml");

  // Write results to XML ---------------------------------------------------------------------------
  echo '<?xml version="1.0"?>';
  echo '<?mso-application progid="Excel.Sheet"?>';
  echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
  echo ' xmlns:o="urn:schemas-microsoft-com:office:office"';
  echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
  echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"';
  echo ' xmlns:html="http://www.w3.org/TR/REC-html40">';
  echo ' <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">';
  echo '  <Title>' . $paper . '</Title>';
  echo '  <Author>TouchStone ' . $ts_version . '</Author>';
  $tmp_start = substr($_GET['startdate'], 6, 2) . '/' . substr($_GET['startdate'], 4, 2) . '/' . substr($_GET['startdate'], 0, 4) . ' ' . substr($_GET['startdate'], 8, 2) . ':' . substr($_GET['startdate'], 10, 2);
  $tmp_end = substr($_GET['enddate'], 6, 2) . '/' . substr($_GET['enddate'], 4, 2) . '/' . substr($_GET['enddate'], 0, 4) . ' ' . substr($_GET['enddate'], 8, 2) . ':' . substr($_GET['enddate'], 10, 2);
  echo '  <Description>Class totals for assessment taken between ' . $tmp_start . ' and ' . $tmp_end .'.</Description>';
  echo '  <LastAuthor>TouchStone ' . $ts_version . '</LastAuthor>';
  echo '  <Created>' . date('Y-m-d', time()) . 'T' . date('H:i:s') . 'Z</Created>';
  echo '  <Company>The University of Nottingham</Company>';
  echo '  <Version>11.6408</Version>';
  echo ' </DocumentProperties>';
  echo ' <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">';
  echo '  <DownloadComponents/>';
  echo '  <LocationOfComponents HRef="file:///E:\"/>';
  echo ' </OfficeDocumentSettings>';
  echo ' <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">';
  echo '  <WindowHeight>13800</WindowHeight>';
  echo '  <WindowWidth>15195</WindowWidth>';
  echo '  <WindowTopX>0</WindowTopX>';
  echo '  <WindowTopY>120</WindowTopY>';
  echo '  <ProtectStructure>False</ProtectStructure>';
  echo '  <ProtectWindows>False</ProtectWindows>';
  echo ' </ExcelWorkbook>';
  echo ' <Styles>';
  echo '  <Style ss:ID="Default" ss:Name="Normal">';
  echo '   <Alignment ss:Vertical="Bottom"/>';
  echo '   <Borders/>';
  echo '   <Font/>';
  echo '   <Interior/>';
  echo '   <NumberFormat/>';
  echo '   <Protection/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s23">';
  echo '   <Font x:Family="Swiss" ss:Bold="1"/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s25">';
  echo '   <Font ss:Color="#FF0000"/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s26">';
  echo '   <NumberFormat ss:Format="0%"/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s27">';
  echo '   <Borders>';
  echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>';
  echo '   </Borders>';
  echo '   <Font x:Family="Swiss" ss:Size="20" ss:Color="#000000" ss:Bold="1"/>';
  echo '   <Interior ss:Color="#CCCCFF" ss:Pattern="Solid"/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s28">';
  echo '   <Borders>';
  echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>';
  echo '   </Borders>';
  echo '   <Interior ss:Color="#CCCCFF" ss:Pattern="Solid"/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s30">';
  echo '   <Borders>';
  echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>';
  echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>';
  echo '   </Borders>';
  echo '   <Font x:Family="Swiss" ss:Bold="1"/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s31">';
  echo '   <Font ss:Color="#008000"/>';
  echo '  </Style>';
  echo '  <Style ss:ID="s69">';
  echo '    <NumberFormat ss:Format="Percent"/>';
  echo ' </Style>';
  echo ' </Styles>';
  echo ' <Worksheet ss:Name="Marks">';
  echo '  <Table ss:ExpandedColumnCount="13" ss:ExpandedRowCount="' . ($user_no + 3) . '" x:FullColumns="1" x:FullRows="1">';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="35"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="80"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="130"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="60"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="60"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="50"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="40"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="40"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="70"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="90"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="60"/>';
  echo '   <Column ss:AutoFitWidth="0" ss:Width="90"/>';
  if ($paper_type == 2) {
    echo '   <Column ss:AutoFitWidth="0" ss:Width="90"/>';
  }
  echo '   <Row ss:AutoFitHeight="0" ss:Height="26.25">';
  echo '    <Cell ss:StyleID="s27"><Data ss:Type="String">' . $paper . '</Data></Cell>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '    <Cell ss:StyleID="s28"/>';
  echo '   </Row>';
  echo '   <Row>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Title</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Surname</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">First Names</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Student ID</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Username</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Module</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Mark</Data></Cell>';
  if ($marking == '0') {
    echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">%</Data></Cell>';
  } else {
    echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Adjusted %</Data></Cell>';
  }
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Classification</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Start Date</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Duration</Data></Cell>';
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">IP Address</Data></Cell>';
  if ($paper_type == 2) {
    echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">Room</Data></Cell>';
  }
  echo '  </Row>';

  $absent_no = 0;
  $xmean2_total = 0;
  for ($i=0; $i<$user_no; $i++) {
    echo '<Row>';
    echo '<Cell><Data ss:Type="String">' . $user_results[$i]['title'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlentities($user_results[$i]['surname']) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlentities($user_results[$i]['first_names']) . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . $user_results[$i]['student_id'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . $user_results[$i]['username'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . $user_results[$i]['module'] . '</Data></Cell>';
    if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.{
      echo '<Cell/>';
      echo '<Cell/>';
      echo '<Cell/>';
      echo '<Cell><Data ss:Type="String">No Attendance</Data></Cell>';
      echo '<Cell/>';
      echo '<Cell/>';
      echo '</Row>';
      $absent_no++;
    } else {
      $temp_percent = $user_results[$i]['adj_percent'];
      if ($temp_percent < $pass_mark) {
        echo '<Cell ss:StyleID="s25"><Data ss:Type="Number">' . $user_results[$i]['mark'] . '</Data></Cell>';
      } else {
        echo '<Cell><Data ss:Type="Number">' . $user_results[$i]['mark'] . '</Data></Cell>';
      }
      $total_time += $user_results[$i]['duration'];
      if ($user_results[$i]['adj_percent'] < $pass_mark) {
        echo '<Cell ss:StyleID="s25"><Data ss:Type="Number">' . $temp_percent . '</Data></Cell>';
        echo '<Cell ss:StyleID="s25"><Data ss:Type="String">Fail</Data></Cell>';
      } else {
        if (isset($ss_hon) and $user_results[$i]['percent'] >= $ss_hon) {
          echo '<Cell ss:StyleID="s31"><Data ss:Type="Number">' . $temp_percent . '</Data></Cell>';
          echo '<Cell ss:StyleID="s31"><Data ss:Type="String">Distinction</Data></Cell>';
        } else {
          echo '<Cell><Data ss:Type="Number">' . $temp_percent . '</Data></Cell>';
          echo '<Cell><Data ss:Type="String">Pass</Data></Cell>';
        }
      }
      echo '<Cell><Data ss:Type="String">' . $user_results[$i]['display_started'] . '</Data></Cell>';
      echo '<Cell><Data ss:Type="String">' . formatsec($user_results[$i]['duration']) . '</Data></Cell>';
      echo '<Cell><Data ss:Type="String">' . $user_results[$i]['ipaddress'] . '</Data></Cell>';
      if ($paper_type = 2) {
        echo '<Cell><Data ss:Type="String">' . $user_results[$i]['room'] . '</Data></Cell>';
      }
      echo '</Row>';
      if ($user_results[$i]['mark'] > $max_mark) {
        $max_mark = $user_results[$i]['mark'];
      }
      if ($user_results[$i]['mark'] < $min_mark) {
        $min_mark = $user_results[$i]['mark'];
      }

      $user_results[$i]['xmean'] = $user_results[$i]['mark'] - ($total_mark / $completed_no);
      $user_results[$i]['xmean2'] = $user_results[$i]['xmean'] * $user_results[$i]['xmean'];

      if ($user_results[$i]['questions'] >= $question_no) {
        $xmean2_total += $user_results[$i]['xmean2'];
      }
    }
  }

  echo '  </Table>';
  echo '  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">';
  echo '   <Print>';
  echo '    <ValidPrinterInfo/>';
  echo '    <PaperSizeIndex>9</PaperSizeIndex>';
  echo '    <HorizontalResolution>600</HorizontalResolution>';
  echo '    <VerticalResolution>600</VerticalResolution>';
  echo '   </Print>';
  echo '   <Selected/>';
  echo '   <FreezePanes/>';
  echo '   <FrozenNoSplit/>';
  echo '   <SplitHorizontal>2</SplitHorizontal>';
  echo '   <TopRowBottomPane>2</TopRowBottomPane>';
  echo '   <ActivePane>2</ActivePane>';
  echo '   <Panes>';
  echo '    <Pane>';
  echo '     <Number>3</Number>';
  echo '    </Pane>';
  echo '    <Pane>';
  echo '     <Number>2</Number>';
  echo '     <ActiveRow>9</ActiveRow>';
  echo '     <ActiveCol>5</ActiveCol>';
  echo '    </Pane>';
  echo '   </Panes>';
  echo '   <ProtectObjects>False</ProtectObjects>';
  echo '   <ProtectScenarios>False</ProtectScenarios>';
  echo '  </WorksheetOptions>';
  echo ' </Worksheet>';
  echo ' <Worksheet ss:Name="Summary">';
  $exp_row_count = 17;
  $exp_row_count += ($marking > 1) ? '2' : $marking;
  $exp_row_count += (count($warnings['deleted_qns']) > 0) ? 1 : 0;
  echo '  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="'.$exp_row_count.'" x:FullColumns="1" x:FullRows="1">';
  echo '  <Column ss:AutoFitWidth="0" ss:Width="120"/>';

  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Students Submitted</Data></Cell>';
  echo '<Cell><Data ss:Type="Number">' . ($user_no - $absent_no) . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Total Possible Marks</Data></Cell>';
  echo '<Cell><Data ss:Type="Number">' . $total_marks . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Pass Mark</Data></Cell>';
  echo '<Cell ss:StyleID="s26"><Data ss:Type="Number">' . ($pass_mark/100) . '</Data></Cell>';
  echo '</Row>';
  if ($marking == '0') {
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Average Mark</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . $mean_mark . '</Data></Cell>';
    echo '</Row>';
  } elseif ($marking == '1') {
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Random Mark</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . number_format($total_random_mark, 1, '.', ',') . '</Data></Cell>';
    echo '</Row>';
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Mean Mark</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . $mean_mark . '</Data></Cell>';
    echo '</Row>';
  } else {
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">SS</Data></Cell>';
    echo '<Cell ss:StyleID="s69"><Data ss:Type="Number">' . ($ss_pass/100) . '</Data></Cell>';
    echo '</Row>';
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">SS Distinction</Data></Cell>';
    echo '<Cell ss:StyleID="s69"><Data ss:Type="Number">' . ($ss_hon/100) . '</Data></Cell>';
    echo '</Row>';
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Mean Mark</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . $mean_mark . '</Data></Cell>';
    echo '</Row>';
  }
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Median Mark</Data></Cell>';
  echo '<Cell><Data ss:Type="Number">' . $median_mark . '</Data></Cell>';
  echo '</Row>';
  if ($completed_no == 1) {
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">StDev Mark</Data></Cell>';
    echo '<Cell><Data ss:Type="String">n/a</Data></Cell>';
    echo '</Row>';
  } else {
    echo '<Row>';
    echo '<Cell ss:StyleID="s23"><Data ss:Type="String">StDev Mark</Data></Cell>';
    echo '<Cell><Data ss:Type="Number">' . number_format($stddev_mark, 2, '.', ',') . '</Data></Cell>';
    echo '</Row>';
  }
    
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Max Mark</Data></Cell>';
  echo '<Cell><Data ss:Type="Number">' . $max_mark . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Min Mark</Data></Cell>';
  echo '<Cell><Data ss:Type="Number">' . $min_mark . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Range</Data></Cell>';
  echo '<Cell><Data ss:Type="Number">' . ($max_mark - $min_mark) . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Top 10%</Data></Cell>';
  echo '<Cell ss:StyleID="s69"><Data ss:Type="Number">' . $top_10/100 . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Top 15%</Data></Cell>';
  echo '<Cell ss:StyleID="s69"><Data ss:Type="Number">' . $top_15/100 . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Top 20%</Data></Cell>';
  echo '<Cell ss:StyleID="s69"><Data ss:Type="Number">' . $top_20/100 . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Top 25%</Data></Cell>';
  echo '<Cell ss:StyleID="s69"><Data ss:Type="Number">' . $top_25/100 . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Bottom 10%</Data></Cell>';
  echo '<Cell ss:StyleID="s69"><Data ss:Type="Number">' . $bottom_10/100 . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Average Time</Data></Cell>';
  echo '<Cell><Data ss:Type="String">' . formatsec(round($total_time / $completed_no,0)) . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Excluded Questions</Data></Cell>';
  echo '<Cell><Data ss:Type="String">' . $display_excluded . '</Data></Cell>';
  echo '</Row>';
  echo '<Row>';
  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Experimental Questions</Data></Cell>';
  echo '<Cell><Data ss:Type="String">' . $display_experimental . '</Data></Cell>';
  echo '</Row>';
  if(count($warnings['deleted_qns']) > 0) {
	  echo '<Row>';
	  echo '<Cell ss:StyleID="s23"><Data ss:Type="String">Warnings</Data></Cell>';
	  echo '<Cell><Data ss:Type="String">Answers found for questions that no longer appear on the paper (IDs';
    for($i = 0; $i < count($warnings['deleted_qns']); $i++) {
     	echo $warnings['deleted_qns'][$i];
      if($i < count($warnings['deleted_qns']) - 1) {
      	echo ", ";
      }
    }
	  echo ')</Data></Cell>';
	  echo '</Row>';
  }
  echo '  </Table>';  
  echo '  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">';
  echo '   <ProtectObjects>False</ProtectObjects>';
  echo '   <ProtectScenarios>False</ProtectScenarios>';
  echo '  </WorksheetOptions>';
  echo ' </Worksheet>';
  echo '</Workbook>';

  $mysqli->close();
?>