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

namespace testing\datagenerator;

use \Config,
    \assessment,
    \UserObject,
    \Exception,
    \PHPUnit_Framework_Assert,
    \UserUtils;

/**
 * Generates Rogo paper.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class questions extends generator {

  /**
   * Create a new question 
   *  Since _fields_required had not been used in question creating process, required fields are hard coded in HTML in webpage....
   *  here have to use hard code sql to create question.
   * 
   * @param array $data mandatory question data
   *
   */
  public function insert_question($data) {
    loader::get('papers');
    $db = loader::get_database();
    $username = $data['user'];
    $userid = UserUtils::username_exists($username, $db);

    $defult = array(
        "leadin" => "test question leadin",
        "display_method" => "vertical",
        "ownerID" => $userid,
        "q_media_width" => 0,
        "q_media_height" => 0,
        "creation_date" => date('Y-m-d H:i:s'),
        "last_edited" => date('Y-m-d H:i:s'),
        "bloom" => null,
        "scenario_plain" => "defult scenario_plain",
        "leadin_plain" => "",
        "checkout_time" => "",
        "checkout_authorID" => "",
        "deleted" => null,
        "locked" => null,
        "std" => "",
        "status" => 1,
        "q_option_order" => "display order",
        "score_method" => "Mark per Option",
        "settings" => "",
        "guid" => uniqid()
    );

    $qdata = $this->set_defaults_and_clean($defult, $data);
    $sqlquery = <<< SQLQUERY
INSERT INTO questions (q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method,
display_method, q_option_order, std, bloom, ownerID, q_media, q_media_width, q_media_height, checkout_time, checkout_authorID,
creation_date, last_edited, locked, deleted, status, settings, guid)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
SQLQUERY;
    try {

      $result = $db->prepare($sqlquery);
      $result->bind_param('ssssssssssssssissssisssssss', $qdata['q_type'], $qdata['theme'], $qdata['scenario'], 
              $qdata['scenario_plain'], $qdata['leadin'], $qdata['leadin_plain'], $qdata['notes'], $qdata['correct_fback'], 
              $qdata['incorrect_fback'], $qdata['score_method'], $qdata['display_method'], $qdata['q_option_order'], $qdata['std'], 
              $qdata['bloom'], $qdata['ownerID'], $qdata['q_media'], $qdata['q_media_width'], $qdata['q_media_height'], $qdata['checkout_time'], 
              $qdata['checkout_authorID'], $qdata['creation_date'], $qdata['last_edited'], $qdata['locked'], $qdata['deleted'], $qdata['status'], 
              $qdata['settings'], $qdata['guid']);
      $result->execute();
      $result->close();
    } catch (Exception $e) {
      echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      echo nl2br($e->getTraceAsString());
      throw new data_error("MySQL error " . $this->_mysqli->error . "<br /> Query:<br /> $sqlquery", $this->_mysqli->errno);
    }
  }

  /**
   * Create a new question 
   * 
   * @parm array $parameters
   *  string parameters[paperowner]
   *  string parameters[type]
   */
  public function create_question($parameters) {

    $types = \QuestionEdit::$types;
    // Basic check mandatory parameters for creating question.
    if (empty($parameters['type']) or ( !in_array($parameters['type'], $types)) or empty($parameters['user']) or empty($parameters['leadin'])) {
      throw new data_error('Must pass list of question type and title ');
    } else {
      $parameters['q_type'] = $parameters['type'];
      unset($parameters['type']);
      $parameters['theme'] = $parameters['leadin'];
      $parameters['leadin_plain'] = $parameters['leadin'];
      $this->insert_question($parameters);
    }
  }

}
