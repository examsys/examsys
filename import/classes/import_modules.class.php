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

namespace import;
use csv\csv_load_exception;

/**
 * Import modules from csv format to rogo db format
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 */
class import_modules extends importer {

  /**
   * The list of modules where school was not found.
   * @var array
   */
  private $moduleexists;

  /**
   * The list of modules added.
   * @var array
   */
  private $moduleadded;

  /**
   * The list of modules that failed to add.
   * @var array
   */
  private $modulefailed;

  /**
   * Required fields
   * @var array
   */
  const REQUIRED = array(
    'moduleid',
    'fullname',
    'school',
  );

  /**
   * Optional feilds
   * @var array
   */
  const OPTIONAL = array(
    'schoolcode',
    'smsapi',
    'objectiveapi',
    'peerreview',
    'externalexaminers',
    'stdset',
    'mapping',
    'active',
    'selfenrol',
    'negmarking',
    'timedexams',
    'questionbasedfb',
    'addteammember',
    'yearstart',
    'externalid',
  );

  /**
   * Filter true/false responses
   * @param $value true/false response
   * @return bool
   */
  private function returnTrueFalse($value) {
    $value = strtolower(trim($value));
    if ($value == 'yes' or $value == 'y' or $value == 'true') {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Do the module import described in the csv file.
   * @throws csv_load_exception
   */
  public function execute() {
    $this->modulefailed = array();
    $this->moduleadded = array();
    $this->moduleexists = array();

    // Set the required headers.
    $this->handler->required_header(self::REQUIRED);
    $default_academic_year_start = $this->config->get_setting('core', 'system_academic_year_start');
    while ($line = $this->handler->get_line()) {
      $line['moduleid'] = trim($line['moduleid']);
      $line['fullname'] = trim($line['fullname']);
      // Check if school exists.
      if (!isset($line['schoolcode'])) {
        $schools = \schoolutils::school_name_exists(trim($line['school']), $this->config->db);
      } else {
        $schools = \schoolutils::get_schoolid_by_code(trim($line['schoolcode']), $this->config->db);
      }
      if ($schools === false) {
        // School not found.
        $this->modulefailed[] = $line['moduleid'];
        continue;
      } else {
        $line['school'] = $schools;
      }
      if (\module_utils::module_exists($line['moduleid'], $this->config->db)) {
        $updateData = array();
        $checklist = '';
        $checked = false;
        if (isset($line['smsapi'])) {
          $updateData['sms'] = trim($line['smsapi']);
        }
        if (isset($line['objectiveapi'])) {
          $updateData['vle_api'] = trim($line['objectiveapi']);
        }
        if (isset($line['peerreview'])) {
          if ($this->returnTrueFalse($line['peerreview']) == true) {
            $checklist .= ',peer';
          }
          $checked = true;
        }
        if (isset($line['externalexaminers'])) {
          if ($this->returnTrueFalse($line['externalexaminers']) == true) {
            $checklist .= ',external';
          }
          $checked = true;
        }
        if (isset($line['stdset'])) {
          if ($this->returnTrueFalse($line['stdset']) == true) {
            $checklist .= ',stdset';
          }
          $checked = true;
        }
        if (isset($line['mapping'])) {
          if ($this->returnTrueFalse($line['mapping']) == true) {
            $checklist .= ',mapping';
          }
          $checked = true;
        }
        if ($checked) {
          $updateData['checklist'] = substr($checklist, 1);
        }
        if (isset($line['active'])) {
          $updateData['active'] = $this->returnTrueFalse($line['active']);
        }
        if (isset($line['selfenrol'])) {
          $updateData['selfenroll'] = $this->returnTrueFalse($line['selfenrol']);
        }
        if (isset($line['negmarking'])) {
          $updateData['neg_marking'] = $this->returnTrueFalse($line['negmarking']);
        }
        if (isset($line['timedexams'])) {
          $updateData['timed_exams'] = $this->returnTrueFalse($line['timedexams']);
        }
        if (isset($line['questionbasedfb'])) {
          $updateData['exam_q_feedback'] = $this->returnTrueFalse($line['questionbasedfb']);
        }
        if (isset($line['addteammember'])) {
          $updateData['add_team_members'] = $this->returnTrueFalse($line['addteammember']);
        }
        if (isset($line['yearstart']) and preg_match ('([0-1][0-9]/[0-3][0-9])', $line['yearstart']) ) {
          $updateData['academic_year_start'] = trim($line['yearstart']);
        }
        if (isset($line['externalid'])) {
          $updateData['externalid'] = trim($line['externalid']);
        }

        $updateData['fullname'] = $line['fullname'];
        $updateData['schoolid'] = $line['school'];

        \module_utils::update_module_by_code($line['moduleid'], $updateData, $this->config->db);
        $this->moduleexists[] = $line['moduleid'];
      } else {
        if (isset($line['smsapi'])) {
          $line['smsapi'] = trim($line['smsapi']);
        } else {
          $line['smsapi'] = null;
        }
        if (isset($line['objectiveapi'])) {
          $line['objectiveapi'] = trim($line['objectiveapi'] );
        } else {
          $line['objectiveapi'] = null;
        }
        if (isset($line['peerreview'])) {
          $line['peerreview'] = $this->returnTrueFalse($line['peerreview']);
        } else {
          $line['peerreview'] = 1;
        }
        if (isset($line['externalexaminers'])) {
          $line['externalexaminers'] = $this->returnTrueFalse($line['externalexaminers']);
        } else {
          $line['externalexaminers'] = 1;
        }
        if (isset($line['stdset'])) {
          $line['stdset'] = $this->returnTrueFalse($line['stdset']);
        } else {
          $line['stdset'] = 0;
        }
        if (isset($line['mapping'])) {
          $line['mapping'] = $this->returnTrueFalse($line['mapping']);
        } else {
          $line['mapping'] = 0;
        }
        if (isset($line['active'])) {
          $line['active'] = $this->returnTrueFalse($line['active']);
        } else {
          $line['active'] = 1;
        }
        if (isset($line['selfenrol'])) {
          $line['selfenrol'] = $this->returnTrueFalse($line['selfenrol']);
        } else {
          $line['selfenrol'] = 0;
        }
        if(isset($line['negmarking'])) {
          $line['negmarking'] = $this->returnTrueFalse($line['negmarking']);
        } else {
          $line['negmarking'] = 1;
        }
        if (isset($line['timedexams'])) {
          $line['timedexams'] = $this->returnTrueFalse($line['timedexams']);
        } else {
          $line['timedexams'] = 0;
        }
        if (isset($line['questionbasedfb'])) {
          $line['questionbasedfb'] = $this->returnTrueFalse($line['questionbasedfb']);
        } else {
          $line['questionbasedfb'] = 1;
        }
        if (isset($line['addteammember'])) {
          $line['addteammember'] = $this->returnTrueFalse($line['addteammember']);
        } else {
          $line['addteammember'] = 1;
        }
        if (isset($line['yearstart']) and preg_match ('([0-1][0-9]/[0-3][0-9])', $line['yearstart']) ) {
          $line['yearstart']= trim($line['yearstart']);
        } else {
          $line['yearstart']= $default_academic_year_start;
        }
        if (isset($line['externalid'])) {
          $line['externalid'] = trim($line['externalid']);
        } else {
          $line['externalid'] = null;
        }
        $success = \module_utils::add_modules(
          $line['moduleid'],
          $line['fullname'],
          $line['active'],
          $line['school'],
          $line['objectiveapi'],
          $line['smsapi'],
          $line['selfenrol'],
          $line['peerreview'],
          $line['externalexaminers'],
          $line['stdset'],
          $line['mapping'],
          $line['negmarking'],
          '',
          $this->config->db,
          0,
          $line['timedexams'],
          $line['questionbasedfb'],
          $line['addteammember'],
          1,
          $line['yearstart'],
          $line['externalid']
        );
        if ($success) {
          $this->moduleadded[] = $line['moduleid'];
        } else {
          $this->modulefailed[] = $line['moduleid'];
        }
      }
    }
  }

  /**
   * Get failed modules
   * @return array
   */
  public function get_failed() {
    return $this->modulefailed;
  }

  /**
   * Get added modules
   * @return array
   */
  public function get_added() {
    return $this->moduleadded;
  }

  /**
   * Get modules that already exist
   * @return array
   */
  public function get_exists() {
    return $this->moduleexists;
  }
}