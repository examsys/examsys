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
* Utility class for user related functions
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once $cfg_web_root . '/classes/dateutils.class.php';

Class UON_SATURN extends SmsUtils {

  public $campus;
  public $url;

  function getUserData($username) {
    $user = array();
    $sources = $this->getStudentSources();
    foreach($sources as $name => $source) {
      if ($source != '') {
        $returned_data = file_get_contents($source . "&username=$username");
        $xml = new SimpleXMLElement($returned_data);
        if ($xml->AttendStatus == 'Studying at the University') {
          $user['StudentID'] = trim($xml->StudentID);
          $user['Title'] = trim($xml->Title);
          $user['Surname'] = trim($xml->Surname);
          $user['Forename'] = trim($xml->Forename);
          $user['CourseCode'] = trim($xml->CourseCode);
          $user['Username'] = '';
          $user['Email'] = trim($xml->Email);
          $user['Gender'] = trim($xml->Gender);
          $user['YearofStudy'] = trim($xml->YearofStudy);
          $user['School'] = trim($xml->School);
          $user['Degree'] = trim($xml->Degree);
          $user['CourseCode'] = trim($xml->CourseCode);
          $user['CourseTitle'] = trim($xml->CourseTitle);
          $user['AttendStatus'] = trim($xml->AttendStatus);
          break; //we have found the student so stop looking
        }
      }
    }

    if (count($user) > 0) {
      return $user;
    } else {
      //no user found return false
      return false;
    }
  }

  function get_module($moduleID) {
    $users = array();

    // Calculate what the current academic session is.
    $session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : date_utils::get_current_academic_year();
    $session_parts = explode('/', $session);
    $replaced_module = str_replace('_UNMC', '', $moduleID);
    $replaced_module = str_replace('_UNNC', '', $replaced_module);


    $returned_data = @file_get_contents($this->url . "&code=$replaced_module&year=" . $session_parts[0]);
    if ($returned_data !== false) {

      $xml = @new SimpleXMLElement($returned_data);
      if (is_object($xml) and !isset($xml->ErrorMessage)) {
        return $xml;
      }
      else {
        return false;
      }
    }
    else {
      return false;

    }
  }

  function get_module_info($moduleID) {
    $xml=$this->get_module($moduleID);
    if (is_object($xml) and !isset($xml->ErrorMessage) and !isset($xml->Module->Error)) {
      $moduletitle=$xml->Module->ModuleTitle;
      $school='SchoolMissing';
      if(isset($xml->School)) {
        if(isset($xml->School->AdministeredBy)) {
          $school=$xml->School->AdministeredBy;
        }
        else {
          $school=$xml->School->ContributedBy;
        }
      }
      return array($moduleID,$moduletitle,$school);
    }
    else {
      return false;
    }
  }
  function getModuleEnrolements($moduleID) {
    $xml=$this->get_module($moduleID);
    foreach ($xml->Module->Membership->Student as $sms) {
      $sms->Title = trim($sms->Title);
      $sms->Surname = trim($sms->Surname);
      $sms->Forename = trim($sms->Forename);
      $sms->CourseCode = trim($sms->CourseCode);
      $sms->Username = trim($sms->Username);
      $sms->Email = trim($sms->Email);
      $sms->Gender = trim($sms->Gender);
      $sms->YearofStudy = trim($sms->YearofStudy);
      $sms->StudentID = trim($sms->StudentID);

      $lookup_username = trim($sms->Username);

      // Make sure we have a proper username - it can sometimes be blank in SATURN data
      if ($sms->Email != '') {
        // Try to extract from email address
        $un_parts = explode('@', $sms->Email);
        $lookup_username = $un_parts[0];
      }
      $users[$lookup_username]=array($sms->Title,$sms->Surname,$sms->Forename,$sms->CourseCode,$sms->Email,$sms->Gender,$sms->YearofStudy,$sms->StudentID);
    }



    if (count($users) > 0) {
      return $users;
    } else {
      //no user found return false
      return false;
    }
  }
  
  function getStudentSources() {
    return array('&lt;No lookup&gt;'=>'','UK'=>'http://saturn-exports.nottingham.ac.uk/touchstonestudent.ashx?campus=uk','Malaysia'=>'http://saturn-exports.nottingham.ac.uk/touchstonestudent.ashx?campus=malaysia','China'=>'http://saturn-exports.nottingham.ac.uk/touchstonestudent.ashx?campus=china');
  }
  
  function getModuleSources() {
    return array('&lt;No lookup&gt;'=>'','UK'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=uk','Malaysia'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia','China'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china');
  }


  function set_module($location) {
    if($location == 'MY') {
      $location = 'Malaysia';
    }
    elseif($location == 'CN') {
      $location = 'China';
    }
    $arr=$this->getModuleSources();
    if(!isset($arr[$location]))
    {
      $this->url='';
      $this->campus=$location;
      return;
    }
    $this->url=$arr[$location];
    $this->campus=$location;
  }

  function get_module_name($modulecode) {
$dat=$this->getModuleEnrolements($modulecode);
  }

}
?>