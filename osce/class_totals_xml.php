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
* Export cohort ratings in Excel 2003 format.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

require_once '../include/demo_replace.inc';
require_once '../include/errors.php';
require_once '../include/sort.inc';
require_once './osce.inc';

$demo = is_demo($userObject);

$paperID   = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate   = check_var('enddate', 'GET', true, false, true);

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;
$repcourse    = (isset($_GET['repcourse'])) ? $_GET['repcourse'] : '%';
$repmodule    = (isset($_GET['repmodule'])) ? $_GET['repmodule'] : '';

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper = $propertyObj->get_paper_title();
$crypt_name = $propertyObj->get_crypt_name();

$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();                                                                                  // Get any questions to exclude.

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli, $string);
$report->load_answers();
$paper_buffer = $report->get_paper_buffer();
$question_no  = $report->get_question_no();

$user_results = load_osce_results($propertyObj, $demo, $configObject, $question_no, $mysqli);
$report->set_user_results($user_results);
$report->generate_stats();
$user_no = $report->get_user_no();

$q_medians = load_osce_medians($mysqli);

if ($propertyObj->get_pass_mark() == 101) {
  $borderline_method = true;
} else {
  $borderline_method = false;
}

if ($borderline_method) {
  $passmark = getBlinePassmk($user_results, $user_no, $propertyObj);
} elseif ($propertyObj->get_pass_mark() == 102) {
  $passmark = 'N/A';
} else {
  $passmark = $propertyObj->get_pass_mark();
}
$distinction_mark = $propertyObj->get_distinction_mark();

set_classification($propertyObj->get_marking(), $user_results, $passmark, $user_no, $string);
rating_num_text($user_results, $user_no, $propertyObj, $string);
$user_results = array_csort($user_results, $sortby, $ordering);

header('Pragma: public');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".xml");

if ($borderline_method) {
  $col_count = 11;
} else {
  $col_count = 10;
}

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
echo '  <Author>Rogo</Author>';
$tmp_start = substr($_GET['startdate'], 6, 2) . '/' . substr($_GET['startdate'], 4, 2) . '/' . substr($_GET['startdate'], 0, 4) . ' ' . substr($_GET['startdate'], 8, 2) . ':' . substr($_GET['startdate'], 10, 2);
$tmp_end = substr($_GET['enddate'], 6, 2) . '/' . substr($_GET['enddate'], 4, 2) . '/' . substr($_GET['enddate'], 0, 4) . ' ' . substr($_GET['enddate'], 8, 2) . ':' . substr($_GET['enddate'], 10, 2);
echo '  <Description>Class totals for assessment taken between ' . $tmp_start . ' and ' . $tmp_end .'.</Description>';
echo '  <LastAuthor>Rogo</LastAuthor>';
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
echo '  <Table ss:ExpandedColumnCount="' . $col_count . '" ss:ExpandedRowCount="' . ($user_no + 3) . '" x:FullColumns="1" x:FullRows="1">';
echo '   <Column ss:AutoFitWidth="0" ss:Width="35"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="80"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="130"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="60"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="50"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="40"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="80"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="100"/>';
echo '   <Column ss:AutoFitWidth="0" ss:Width="90"/>';
echo '   <Row ss:AutoFitHeight="0" ss:Height="26.25">';
echo '    <Cell ss:StyleID="s27"><Data ss:Type="String">' . $paper . '</Data></Cell>';
echo '    <Cell ss:StyleID="s28"/>';
echo '    <Cell ss:StyleID="s28"/>';
echo '    <Cell ss:StyleID="s28"/>';
echo '    <Cell ss:StyleID="s28"/>';
echo '    <Cell ss:StyleID="s28"/>';
echo '    <Cell ss:StyleID="s28"/>';
if ($borderline_method) {
  echo '    <Cell ss:StyleID="s28"/>';
}
echo '    <Cell ss:StyleID="s28"/>';
echo '    <Cell ss:StyleID="s28"/>';
echo '    <Cell ss:StyleID="s28"/>';
echo '   </Row>';
echo '   <Row>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['title'] . '</Data></Cell>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['surname'] . '</Data></Cell>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['firstnames'] . '</Data></Cell>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['studentid'] . '</Data></Cell>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['course'] . '</Data></Cell>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['total'] . '</Data></Cell>';
if ($borderline_method) {
  echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['rating'] . '</Data></Cell>';
}
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['classification'] . '</Data></Cell>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['starttime'] . '</Data></Cell>';
echo '    <Cell ss:StyleID="s30"><Data ss:Type="String">' . $string['examiner'] . '</Data></Cell>';
echo '  </Row>';

$absent_no = 0;
$xmean2_total = 0;
for ($i=0; $i<$user_no; $i++) {
  echo '<Row>';
  echo '<Cell><Data ss:Type="String">' . $user_results[$i]['title'] . '</Data></Cell>';
  echo '<Cell><Data ss:Type="String">' . htmlentities($user_results[$i]['surname']) . '</Data></Cell>';
  echo '<Cell><Data ss:Type="String">' . htmlentities($user_results[$i]['first_names']) . '</Data></Cell>';
  echo '<Cell><Data ss:Type="String">' . $user_results[$i]['student_id'] . '</Data></Cell>';
  echo '<Cell><Data ss:Type="String">' . $user_results[$i]['grade'] . '</Data></Cell>';
  if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.{
    echo '<Cell/>';
    echo '<Cell/>';
    echo '<Cell><Data ss:Type="String">No Attendance</Data></Cell>';
    echo '<Cell/>';
    echo '</Row>';
    $absent_no++;
  } else {
    echo '<Cell><Data ss:Type="Number">' . $user_results[$i]['numeric_score'] . '</Data></Cell>';
    if ($borderline_method) {
      echo '<Cell><Data ss:Type="String">' . $user_results[$i]['rating'] . '</Data></Cell>';
    }
    echo '<Cell><Data ss:Type="String">' . $user_results[$i]['classification'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . $user_results[$i]['display_started'] . '</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . $user_results[$i]['examiner'] . '</Data></Cell>';
    echo '</Row>';
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
echo '     <ActiveRow>1</ActiveRow>';
echo '     <ActiveCol>2</ActiveCol>';
echo '    </Pane>';
echo '   </Panes>';
echo '   <ProtectObjects>False</ProtectObjects>';
echo '   <ProtectScenarios>False</ProtectScenarios>';
echo '  </WorksheetOptions>';
echo ' </Worksheet>';
echo '</Workbook>';

$mysqli->close();
?>