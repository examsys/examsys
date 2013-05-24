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
 * The UoN Saturn Translation for XML to Rogo Internal format
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once 'outline_lookup.class.php';

include_once $configObject->get('cfg_web_root') . 'lang/en/include/common.inc';


class UoNSaturnTranslation_lookup extends outline_lookup {

  public $impliments_api_lookup_version = 1;
  public $version = 0.9;

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'usertranslatelookup'), 'usertranslatelookup', $this->number, $this->name);


    return $callbackarray;
  }

  function usertranslatelookup($userlookupobj) {

    $this->savetodebug('Running user translate lookup in UoN Saturn Translate');

    // this is on the search data (also used for 1 record lookup)
    $userlookupobj->lookupdata = $this->usertranslate($userlookupobj->lookupdata);


    //this is for multiple blocks
    if (isset($userlookupobj->lookupdatas)) {
      foreach ($userlookupobj->lookupdatas as $key => $value) {
        $userlookupobj->lookupdatas[$key] = $this->usertranslate($userlookupobj->lookupdatas[$key]);
      }
    }

    return $userlookupobj;
  }

  function usertranslate($datapart) {

    if (isset($datapart->role) and $this->orsearchlist($datapart->role, array('Undergraduate', 'Postgraduate', 'UG', 'PGT', 'PG'))) {
      $this->savetodebug('Detected Student, correcting role');
      $datapart->role = 'Student';
    }

    if ((isset($datapart->role) and $this->orsearchlist($datapart->role, array('S'))) or isset($datapart->staffID)) {
      $this->savetodebug('Detected staff, correcting role and filling in fields');
      $datapart->role = 'Staff';
      $datapart->coursecode = 'University Lecturer';
      $datapart->yearofstudy = 1;
    }

    if (isset($datapart->studentID)) {
      $this->savetodebug('Detected Possible Student, correcting role for safety');
      $datapart->role = 'Student';
    }

    if (isset($datapart->attendstatus) and strpos($datapart->attendstatus, 'Suspended') !== false) {
      $this->savetodebug('status is suspended diasbling');
      $datapart->disabled = true;
    }

    if (isset($datapart->gender) and $datapart->gender == 'M') {
      $datapart->gender = 'Male';
    }
    if (isset($datapart->gender) and $datapart->gender == 'F') {
      $datapart->gender = 'Female';
    }


    if (isset($datapart->title) and !isset($datapart->gender)) {
      if (stripos($datapart->title, 'Mr') !== false) {
        $datapart->gender = 'Male';
      }
      if (stripos($datapart->title, 'Ms') !== false or stripos($datapart->title, 'Miss') !== false or stripos($datapart->title, 'Mrs') !== false) {
        $datapart->gender = 'Female';
      }
    }

    return $datapart;
  }

  function orsearchlist($field, $text) {
    $found = false;
    foreach ($text as $value) {
      if ($field == $value) {
        $found = true;
      }
    }

    return $found;
  }

}
