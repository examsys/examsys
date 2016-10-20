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
 * Handles UoN LTI Integration in Rogo
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
class lti_uon_integration_extended extends lti_integration {
    
  const CS_MODULE_SPACE = "/(?P<module>[A-Z]{4}[F1-5][0-9]{3})-(?P<offering>[0-9]{1,2})-(?P<campus>UNNC|UNUK|UNMC)-(?P<semster>[A-Z]{3})-(?P<year>[0-9]{4})$/";
  const CS_NON_MODULE_SPACE = "/^((?P<school>[A-Z]{2,4})-)?(?P<module>[0-9A-Z-]{1,25})-(?P<campus>UNNC|UNUK|UNMC|CN|MY|UK)-(?P<year>[0-9]{4})$/";
  const CS_META_MODULE_CHECK = "/^!((?P<school>[A-Z]{2,4})-)?(?P<module>[0-9A-Z-]{1,25})-(?P<campus>UNNC|UNUK|UNMC|CN|MY|UK)-(?P<year>[0-9]{4})$/";
  
  private $dept_code = array('MS' => 'Surgery', 'CC' => 'ACS', 'AA' => 'American & Canadian Studies',
    'AC' => 'Archaeology', 'LA' => 'Urban Planning', 'AD' => 'Art History', 'MB' => 'Physiology & Pharmacology',
    'ST' => 'Biosciences', 'AL' => 'CELE', 'EC' => 'Chemical Engineering', 'EN' => 'Mining Engineering',
    'PC' => 'Chemistry', 'MC' => 'Public Health Medicine & Epidemiology', 'MG' => 'Obstetrics, Midwifery & Gynaecology',
    'LI' => 'Trent Institute for Health Services Research', 'EV' => 'Structures', 'AB' => 'Classics', 'MR' => 'Pathology',
    'PS' => 'Computer Science', 'LC' => 'Contemporary Chinese Studies', 'MZ' => 'Medicine', 'TT' => 'PGCE',
    'AJ' => 'Critical Theory', 'RN' => 'Cultural Studies', 'LE' => 'Economics', 'EE' => 'Electrical & Electronic Engineering',
    'EZ' => 'Engineering', 'IS' => 'Engineering Surveying & Space Geodesy', 'AE' => 'English', 'AR' => 'Modern Languages',
    'EP' => 'Manufacturing Engineering & Operational Management', 'AF' => 'French', 'LQ' => 'Sociology',
    'LG' => 'Geography', 'AG' => 'German', 'BR' => 'Training & Staff Development Unit', 'AS' => 'Portuguese',
    'AH' => 'History', 'IT' => 'Information Technology', 'RH' => 'Institute of Hearing Research',
    'NI' => 'Institute of Infections and Immunity', 'LW' => 'Institute of Work, Health & Organizations', 
    'OI' => 'International Office', 'UL' => 'Language Centre', 'LL' => 'Law', 'PL' => 'Life & Env Sciences',
    'EM' => 'Materials Engineering & Materials Design', 'PM' => 'Theoretical Mechanics', 'EA' => 'Mechanical Engineering',
    'AM' => 'Music', 'ZN' => 'Ningbo', 'SHS' => 'Nursing', 'PA' => 'Pharmacy', 'AP' => 'Philosophy', 'PP' => 'Physics',
    'LD' => 'Politics', 'LP' => 'Psychology', 'AV' => 'Slavonic Studies', 'AT' => 'Theology', 'SV' => 'Vet School');

  /**
   * Process module information from saturn based naming convnetion 
   * @param mysqli $mysqlidb connection
   * @param string $moduleshortcode module shortcode from VLE
   * @param string $course_title module title from VLE
   * @return array rogo module information
   */
  private function process_cs_naming_convention($mysqli, $moduleshortcode, $course_title = ' ') {
    // only get the shortname through  (courseID is only probably accessible via specific moodle webservices api
    $data = array();
    $fin = strlen($course_title);
    if (strpos($course_title, '(') !== false) $fin = strpos($course_title, '(') - 1;
    $course_title = substr($course_title, 0, $fin);
    if ($course_title == ' ') {
      $course_title = 'MISSING COURSE TITLE';
    }
    // Meta modules not supported.
    if (preg_match(self::CS_META_MODULE_CHECK, $moduleshortcode)) {
        return $data;
    }
    // Module name space.
    // Regular expression to match XXXXYYYY-Z-AAAA-BBB-CCCC occurences in module shortcode where XXXXYYYY is the module code, Z is the offering,
    // AAAA is the campus. B is the semester and CCCC the academic year. We only care about the module code and campus.
    preg_match(self::CS_MODULE_SPACE, $moduleshortcode, $info);
    if (count($info) > 0) {
      $i = 0;
      if ($info['campus'] != 'UNUK') {
        $info['module']  .= '_' . $info['campus'];
      }
      $data[] = array('SMS', $info['module'] , $info['campus'], 'UNKNOWN School', 0, $course_title);
    }
    if (count($data) == 0) {
      // Non module name space.
      // Regeular expression to match ZZZ-XXXX-YYYYYYYYYYYYYYYYYYYYYYYYYYYYY-AAAA-BBBB occurences in module shortcode where XXXX-YYYY is the module code,
      // AAAA is the campus. BBBB is the academic year. ZZZ is the school (this is optional). We only care about the school, module code and campus.
      preg_match(self::CS_NON_MODULE_SPACE, $moduleshortcode, $info);
      if (count($info) > 0) {
        if ($info['campus'] != 'UNUK' and $info['campus'] != 'UK') {
          if ($info['campus'] == 'UNMC' or $info['campus'] == 'MY' ) {
            $info['module']  .= '_UNMC';
            $info['campus'] = 'UNMC';
          } elseif ($info['campus'] == 'UNNC' or $info['campus'] == 'CN') {
            $info['module']  .= '_UNNC';
            $info['campus'] = 'UNNC';
          }
        } else {
          $info['campus'] = 'UNUK';
        }
        // Try to place the module in a school.
        $schoolname = 'UNKNOWN School';
        if (!empty($info['school'])) {
          if (isset($this->dept_code[$info['school']])) {
            $schoolname = $this->dept_code[$info['school']];
          }
        }
        $data[] = array('Manual', $info['module'] , $info['campus'], $schoolname, 1, $course_title);
      }
    }
 
    return $data;
  }

  /**
   * Process module information from saturn based naming convnetion 
   * @param mysqli $mysqlidb connection
   * @param string $moduleshortcode module shortcode from VLE
   * @param string $course_title module title from VLE
   * @return array|bool rogo module information or false on invalid module short code
   */
  private function process_saturn_naming_convention($mysqli, $moduleshortcode, $course_title = ' ') {
    // only get the shortname through  (courseID is only probably accessible via specific moodle webservices api
    // shortname for real module try XXXXXX-YY-ZZZWWWW  WHERE XXXXXX is saturn code YY is country rest we dont care about.
    // shortname for non module VV-XXXXX-XXXXX-YY-WWWW WHERE XXXXXXXXXX is the fake 'module code'  YYY is country VV is DEPT 2 letter code
    // shortname for metamodules is XXXXXX-YY-XXXXXX-YY-XXXXXXX-YYY-ZZZWWWWW where the set of XXXXXX, YY are unknown
    $exploded = explode('-', $moduleshortcode);
    $length = strlen($exploded[0]);
    $fin = strlen($course_title);

    if (strpos($course_title, '(') !== false) $fin = strpos($course_title, '(') - 1;
    $course_title = substr($course_title, 0, $fin);
    if ($length < 6) {
      //not saturn code
      $campus = '';
      //this should mean its a fake course
      $modcode = '';
      for ($a = 1; $a < count($exploded); $a++) {
        if (in_array(strtoupper($exploded[$a]), array('UK', 'MY', 'CN'))) {
          $campus = strtoupper($exploded[$a]);
          break;
        }
        $modcode = $modcode . '-' . $exploded[$a];
      }
      $modcode = substr($modcode, 1);
      $schoolname = 'UNKNOWN School';
      if (isset($this->dept_code[$exploded[0]])) {
        $schoolname = $this->dept_code[$exploded[0]];
      }
      $selfreg = 1;
      if ($course_title == ' ') {
        $course_title = 'MISSING: ';
      }
      $data[] = array('Manual', $modcode, $campus, $schoolname, $selfreg, $course_title);


    } else {
      $a = 0;
      $b = 0;
      $data = array();
      $selfreg = 0;
      while (isset($exploded[$a])) {
        if (strlen($exploded[$a]) == 6) {
          //saturn codes are 6 chars
          // data is

          $data[$b++] = array('SMS', $exploded[$a], 'CampusMissing', 'UNKNOWN School', $selfreg, "MISSING:$course_title");
        } elseif (strlen($exploded[$a]) == 2) {
          // probably campus check
          if (in_array(strtoupper($exploded[$a]), array('UK', 'MY', 'CN'))) {
            for ($c = 0; $c < $b; $c++) {
              if ($data[$c][2] == 'CampusMissing') {
                $data[$c][2] = strtoupper($exploded[$a]);
              }
            }
          }
        }
        $a++;
      }
    }

    foreach ($data as $k => $v) {

      if (substr($v[5], 0, 8) == 'MISSING:' and $v[0] == 'SMS') {
        $sms = SmsUtils::GetSmsUtils();
        if ($sms === false) {
          $data[$k][5] = "SATURN " . $data[$k][5];
        } else {
          $sms->set_module($v[2]);
          $returned = $sms->get_module_info($v[1], $mysqli);
          if ($returned !== false) {
            $data[$k][5] = $returned[1];
            $data[$k][3] = $returned[2];
          } else {
            $data[$k][5] = "SATURN " . $data[$k][5];
          }
        }
      }

      if ($data[$k][1] == '') {
        return false;
      }

      if ($v[2] == 'MY') {
        $data[$k][1] = $data[$k][1] . '_UNMC';
      } elseif ($v[2] == 'CN') {
        $data[$k][1] = $data[$k][1] . '_UNNC';
      }

    }
    if (count($data) == 1 and substr($data[0][5], 0, 8) == 'MISSING:' and strlen($data[0][5]) > 9) {
      $data[0][5] = substr($data[0][5], 8);
    }
    return $data;
  }

  /**
   * Check last time logged in and decide if re-authentication should be done
   * @param string $time last time logged in
   * @return bool true if user require re-authentication 
   */
  public function user_time_check($time) {
    $time1 = strtotime($time);
    $time2 = time();
    $timediff = $time2 - $time1;
    if ($timediff > $this->config->get_setting('core', 'lti_auth_timeout')) {
      return true;
    }
    return false;
  }

  /**
   * Returns the sms url appropriate for the item element, will insert an error into the sys log if SMS is not set up correctly.
   * @param array $data module data from module_code_translate
   * @return string|bool SMS url or false on exception
   */
  public function sms_api($data) {

    if ($data[0] != 'SMS') {
      return '';
    }
    $SMS = SmsUtils::GetSmsUtils();
    if ($SMS === false) {
      // Attempting to create a module not via the SMS - exception.
      return false;
    } else {
      $SMS->set_module($data[2]);
			
      return $SMS->url;
    }

  }

  /**
   * Convert VLE module shortcode into Rogo moduleid 
   * @param mysqli $mysqli db connection
   * @param string $moduleshortcode VLE module shortcode
   * @param string $course_title VLE module title
   * @return array rogo module information or false on invalid module short code
   */
  public function module_code_translate($mysqli, $moduleshortcode, $course_title = ' ') {

    if (stripos($moduleshortcode, ' ') !== false) {
      return false;
    }
    
    // Different process depending on naming convention.
    if (preg_match(self::CS_MODULE_SPACE, $moduleshortcode) or preg_match(self::CS_NON_MODULE_SPACE, $moduleshortcode)
        or preg_match(self::CS_META_MODULE_CHECK, $moduleshortcode)) {
      // CS naming convention.
      $data = $this->process_cs_naming_convention($mysqli, $moduleshortcode, $course_title);
    } else {
      // Saturn naming convention.
      $data = $this->process_saturn_naming_convention($mysqli, $moduleshortcode, $course_title);
    }
    
    // return the data
    // returning an array containing an array, description of inner array
    // first is 'Manual' or 'SMS' indicating if its not or it is a manual add or a live SMS based module
    // second is the module code
    // third is campus
    // fourth is School it belongs to as text
    // fifth is if its self registration module
    // sixth is the module title.  if it starts MISSING: then there is need for manual intervention to complete this correctly

    if (count($data) === 0) {
      return false;
    }

    return $data;
  }
}
