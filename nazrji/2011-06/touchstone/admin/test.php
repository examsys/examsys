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
* Script to obtain module enrolements from Student Management System (SMS). Run via a cron job.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  // Only run from the command line!
  //if (PHP_SAPI != 'cli') {
  //  die("Please run this test from CLI!\n");
  //}

  set_time_limit(0);
  
  require_once '/var/www/touchstone/touchstone/config/config.inc';
  
  $session = '2010/11';
    
  $session_parts = explode('/',$session);
  
    $sms = "http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia";
    $module = 'MM1EM1';
    $enrolements = 0;
    $deletions = 0;
    $enrolement_details = '';
    $deletion_details = '';

    // Look up SMS
    $returned_data = file_get_contents($sms . "&code=$module&year=" . $session_parts[0]);
    $xml = new SimpleXMLElement($returned_data);
    
    echo "<table>\n";
    if (is_object($xml) and !isset($xml->ErrorMessage)){
      foreach ($xml->Module->Membership->Student as $sms) {
        $sms->Title = trim($sms->Title);
        $sms->Surname = trim($sms->Surname);
        $sms->Forename = trim($sms->Forename);
        $sms->CourseCode = trim($sms->CourseCode);
        $sms->Username = trim($sms->Username);
        $sms->Email = trim($sms->Email);
        $sms->Faculty = trim($sms->Faculty);
        $sms->Gender = trim($sms->Gender);
        $sms->YearofStudy = trim($sms->YearofStudy);
        echo "<tr><td>" . $sms->Title . "</td><td>" . $sms->Surname . "</td><td>" . $sms->Forename . "</td><td>" . $sms->Email . "</td></tr>\n";
      }
    }
    echo "</table>\n";
    

?>