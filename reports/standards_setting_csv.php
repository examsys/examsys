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
* Standards Setting report in CSV format.
*
* @author Richard Whitefoot (UEA)
* @version 1.0
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/std_set_shared_functions.inc';
require_once '../classes/paperproperties.class.php';

$displayDebug = false; //disable debud output in this script as it effects the output

/**
 * Output standards setting review line in CSV format
 * @param array $review - review details
 * @param array $string - language settings
 * @return string
 */
function displayReviewCsv($review, $string) {

  if ($review['review_total'] == $review['total_marks']) {
    $rowOutcome = 'Ok';
  } else {
    $rowOutcome = 'Review Total != Total Marks';
  }

  if ($review['group_review'] != 'No') {
    $rowOutcome = "Group review";
  }
  
  $output = '';
  $output = addslashes($rowOutcome) . ",";

  if ($review['distinction_score'] != 'n/a') {
    $review['distinction_score'] .= '%';
  }

  if ($review['group_review'] != 'No') {
    $output .= "Group review,";
  } else {
    $output .= addslashes($review['name']) . ",";
  }
  if ($review['distinction_score'] == '0.000000%') {
    $review['distinction_score'] = 'top 20%';
  }

  $output .= addslashes($review['display_date']) . ",";
  $output .= addslashes($review['pass_score']) . ",";
  $output .= addslashes($review['distinction_score']) . ",";
  $output .= addslashes($review['review_total']) . ",";
  $output .= addslashes($review['total_marks']) . ",";
  $output .= addslashes($review['method']) . ",";

  $output .= "\n";

  return $output;
}

$paperID    = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli, $string);

$total_mark   = $propertyObj->get_total_mark();

$reviews = get_reviews($mysqli, 'index', $paperID, $total_mark, $no_reviews);

$csv = '';

header('Pragma: public');
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $_GET['paperID']) . "_standards_setting.csv");

$percent_decimals = $configObject->get('percent_decimals');

if (is_array($reviews)) {

  $csv .= addslashes($string['validate']) . ",";
  $csv .= addslashes($string['standardsetter']) . ",";
  $csv .= addslashes($string['date']) . ",";
  $csv .= addslashes($string['passscore']) . ",";
  $csv .= addslashes($string['distinction']) . ",";
  $csv .= addslashes($string['reviewmarks']) . ",";
  $csv .= addslashes($string['papertotal']) . ",";
  $csv .= addslashes($string['method']) . ",";
  $csv .= "\n";

  $csv .= ",,,,,,,\n";

  foreach ($reviews as $review) {
    $csv .= displayReviewCsv($review, $string);
  }

} else {
  $csv .= strip_tags($string['nostandardsset']);
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();
?>