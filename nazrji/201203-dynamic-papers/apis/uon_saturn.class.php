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
  
  function getModuleEnrolements($moduleID) {
    $users = array();
    
    // Calculate what the current academic session is.
    $session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : DateUtils::get_current_academic_year();
    $session_parts = explode('/',$session);
    
    if (count($users) > 0) {
      return $users;
    } else {
      //no user found return false
      return false;
    }
  }
  
  function getStudentSources() {
    return array('&lt;No lookup&gt;'=>'','UK'=>'http://saturn-exports.nottingham.ac.uk/touchstonestudent.ashx?campus=uk');
  }
  
  function getModuleSources() {
    return array('&lt;No lookup&gt;'=>'','UK'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=uk','Malaysia'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia','China'=>'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china');
  }
  
}
?>