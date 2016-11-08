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
*
* @author Joseph Baxter
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

/**
 * Class for class_totals functions used in summative exam check test.
 */
class class_totals {

  /**
   * Function to get the html from a page we wish to scrape.
   *
   * @param string $url - the page we wish to scrape
   * @param string $username - the user we are to login with
   * @param string $password - the users password
   * @return string $output - page response html
   */
  function getData($url, $username, $password) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POSTFIELDS, "ROGO_USER=" . $username . "&ROGO_PW=" . $password . "&rogo-login-form-std=SignIn");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: en-us,en;q=0.5'));

    $output = curl_exec($ch);
    curl_close($ch);

    if (strpos($output,'<title>Log In</title>') !== false) {
      //V4.4 needing authentication
      $output = null;
    }
    return $output;
  }

  /**
   * Function to strip out image tag from the mark and percent columns in reports/class_totals.php.
   * @param string $line
   * @return string
   */
  function tidyLine($line) {
    $line = str_replace('<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="Marking not complete" />&nbsp;', '', $line);
    $parts = explode('>', $line);
    $parts2 = explode('<', $parts[1]);

    return str_replace('&nbsp;', '', $parts2[0]);
  }

  /**
   * Function to parse marks on reports/class_totals.php.
   *
   * @param string $data - html from page we are scraping
   * @return array $marks
   */
  function parseRawMarks($data) {
    // No result
    if ($data == NULL) {
      return false;
    }
    // Asking for authentication
    if (strpos($data, 'rogo-login-form-std')) {
      return false;
    }
    $marks = array();
    $line = 0;
    $data_line = explode('<tr', $data);

    foreach ($data_line as $row) {
      if (strpos($row, ' id="res') !== false) {
        $cols = explode('<td', $row);

        $tmp_parts = explode("setVars('", $cols[0]);

        $tmp_parts2 = explode(',', $tmp_parts[1]);
        $tmp_userID = $tmp_parts2[1];

        $marks[$line]['mark'] = $this->tidyLine($cols[7]);
        $marks[$line]['percent'] = $this->tidyLine($cols[8]);
        $marks[$line]['metadataID'] = str_replace("'", "", $tmp_parts2[0]);
        $marks[$line]['userID'] = $tmp_userID;

        $line++;
      }
    }
    return $marks;
  }

  /**
   * Function to parse marks on paper/finish.php
   *
   * @param string $data - html from page we are scraping
   * @return float $mark
   */
  function parseScript($data) {
    if ($data == NULL) {
      return false;
    }
    $main_data = explode('<body', $data);

    $data_line = explode('<tr', $main_data[1]);

    foreach ($data_line as $row) {
            $found = strpos($row, 'Your mark');
      if ($found !== false) {
        $cols = explode('>', $row);

        $parts = explode(' out of', $cols[4]);
        $mark = round($parts[0],1);  // Round it to 1 decimal because this is what Class Totals does.
      }
    }
    return $mark;
  }

  /**
   * Function to get all papers completed in time frame to scrape for marks, and then compare the class_totals and finish reports.
   *
   * @param type $mysqli - database object
   * @param type $username - user for db access
   * @param type $password - the users password
   * @param type $rootpath - root path of site
   * @param type $userid - the user running the script
   * @param type $start_dateSQL - start date range of papers checked
   * @param type $end_dateSQL - end date range of papers checked
   * @param type $server - the server we are checking
   * @param type $paperid - the papers we want to check (optional, all if not supplied)
   */
  public function process_papers($mysqli, $username, $password, $rootpath, $userid, $start_dateSQL, $end_dateSQL, $server, $paperid = '') {

    $papers = array();

    if ($paperid != '') {
      $result = $mysqli->prepare("SELECT crypt_name, property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE property_id = ?");
      $result->bind_param('i', $paperid);
    } else {
      $result = $mysqli->prepare("SELECT crypt_name, property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE paper_type = '2' AND start_date > $start_dateSQL AND end_date < $end_dateSQL AND deleted IS NULL ORDER BY start_date");
    }
    $result->execute();
    $result->bind_result($crypt_name, $paperID, $title, $display_start_date, $start_date, $end_date);
    while ($result->fetch()) {
      $papers[] = array('crypt_name'=>$crypt_name, 'paperID'=>$paperID, 'title'=>$title, 'display_start_date'=>$display_start_date, 'start_date'=>$start_date, 'end_date'=>$end_date);
    }
    $result->close();

    $paper_no = count($papers);
    $current_no = 0;

    $result = $mysqli->prepare("DELETE FROM class_totals_test_local WHERE user_id = ?");
    $result->bind_param('i', $userid);
    $result->execute();

    $result = $mysqli->prepare("SELECT surname, first_names, username FROM users WHERE id = ? LIMIT 1");
    foreach ($papers as $paper) {
      $url = $server . $rootpath . "/reports/class_totals.php?paperID=" . $paper['paperID'] . "&startdate=" . $paper['start_date'] . "&enddate=" . $paper['end_date'] . "&repmodule=&repcourse=%&sortby=student_id&module=1&folder=&percent=100&absent=0&direction=asc&studentsonly=1";

      $output = $this->getData($url, $username, $password);
      $marks_set = $this->parseRawMarks($output);

      $current_no++;

      $insert = $mysqli->prepare("INSERT INTO class_totals_test_local(user_id, paper_id, status) VALUES(?, ?, 'in_progress')");
      $insert->bind_param('ii', $userid, $paper['paperID']);
      $insert->execute();
      $insert->close();

      $errors = '';
      if ($marks_set === false) {
        $marks_set = array();
        $errors = "<ul><li>Couldn't access class_totals</li>\n";
      }
      foreach ($marks_set as $mark) {
        $url = $server . $rootpath . "/paper/finish.php?id=" . $paper['crypt_name'] . "&metadataID=" . $mark['metadataID'] . "&userID=" . $mark['userID'] . "&surname=Test&log_type=2&percent=" . str_replace('%' ,'', $mark['percent']) . "&disable_mappings=1";
        $output = $this->getData($url, $username, $password);
        $script_mark = $this->parseScript($output);

        if ($script_mark === false) {
          if ($errors == '') {
            $errors = '<ul>';
          }
          $errors .= "<li>Couldn't access finish</li>\n";
        }

        if ($script_mark != $mark['mark']) {
          $result->bind_param('i', $mark['userID']);
          $result->execute();
          $result->store_result();
          $result->bind_result($tmp_surname, $tmp_first_names, $tmp_username);
          $result->fetch();

          if ($errors == '') {
            $errors = '<ul>';
          }
          $errors .= "<li>Problem with " . $mark['userID'] . " $tmp_surname, $tmp_first_names ($tmp_username) - $script_mark / " . $mark['mark'] . "</li>";
        }
      }

        if ($errors != '') {
          $errors .= '</ul>';
          $status = 'failure';
        } else {
          $status = 'success';
        }

        $update = $mysqli->prepare("UPDATE class_totals_test_local SET status = ?, errors = ? WHERE user_id = ? AND paper_id = ?");
        $update->bind_param('ssii', $status, $errors, $userid, $paper['paperID']);
        $update->execute();
        $update->close();
      }
      $result->close();
  }
}
?>
