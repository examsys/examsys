<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * The UoN Saturn Translation for XML to ExamSys Internal format
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_lookup.class.php';
$cfg_web_root = $configObject->get('cfg_web_root');
include_once $configObject->get('cfg_web_root') . 'lang/en/include/common.php';

class UoNSaturnTranslation_lookup extends outline_lookup
{
    public $impliments_api_lookup_version = 1;
    public $version = 0.9;

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'usertranslatelookup'), 'usertranslatelookup', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'moduletranslatelookup'), 'moduletranslatelookup', $this->number, $this->name);
        return $callbackarray;
    }

    public function moduletranslatelookup($modulelookupobj)
    {
        $this->savetodebug('Running module translate lookup in UoN Saturn Translate');
        // this is on the search data (also used for 1 record lookup)
        if (isset($modulelookupobj->lookupdata)) {
            $modulelookupobj->lookupdata = $this->moduletranslate($modulelookupobj->lookupdata);
        }

        //this is for multiple blocks
        if (isset($modulelookupobj->lookupdatas)) {
            foreach ($modulelookupobj->lookupdatas as $key => $value) {
                    $modulelookupobj->lookupdatas[$key] = $this->moduletranslate($modulelookupobj->lookupdatas[$key]);
            }
        }

        return $modulelookupobj;
    }

    public function moduletranslate($datapart)
    {
        if (isset($datapart->rawschools)) {
            //detect raw xml school info

            foreach ($datapart->rawschools->School as $v) {
                if (isset($v->AdministeredBy)) {
                          $school = (string)$v->AdministeredBy;
                          break;
                }
                if (isset($v->ContributedToBy)) {
                    $school = (string)$v->ContributedToBy;
                }
            }

            if (isset($school)) {
                $datapart->school = $school;
                $this->savetodebug('School Set to ' . $school);
            }
        }

        if (isset($datapart->rawmembership)) {
            $fields = array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus','Faculty' => 'faculty', 'ReasonForLeaving' => 'reasonforleaving', 'Username' => 'username');
            $students = array();
            foreach ($datapart->rawmembership->Student as $v) {
                    $studenti = new stdClass();
                foreach ($fields as $keyf => $keyo) {
                    if (isset($v->$keyf)) {
                            $studenti->$keyo = (string)$v->$keyf;
                    }
                }
                $studenti = $this->usertranslate($studenti);
                $students[] = $studenti;
            }

            if (count($students) > 0) {
                $datapart->students = $students;
            }
        }


        return $datapart;
    }

    public function usertranslatelookup($userlookupobj)
    {
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

    public function usertranslate($datapart)
    {
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

        if (isset($datapart->attendstatus) and mb_strpos($datapart->attendstatus, 'Suspended') !== false) {
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
            if (mb_stripos($datapart->title, 'Mr') !== false) {
                    $datapart->gender = 'Male';
            }
            if (mb_stripos($datapart->title, 'Ms') !== false or mb_stripos($datapart->title, 'Miss') !== false or mb_stripos($datapart->title, 'Mrs') !== false) {
                $datapart->gender = 'Female';
            }
        }

        if (isset($datapart->studentstatus)) {
            if (mb_strtoupper(mb_substr($datapart->reasonforleaving, 0, 3)) == 'W/D') {
                    $datapart->role = 'left';
            } elseif (mb_stripos($datapart->reasonforleaving, 'not permitted to progress') !== false) {
                  $datapart->role = 'left';
            } elseif ($datapart->reasonforleaving == 'Successfully completed course') {
                $datapart->role = 'graduate';
            } else {
                $datapart->role = 'Student';
            }
        }
        return $datapart;
    }

    public function orsearchlist($field, $text)
    {
        $found = false;
        foreach ($text as $value) {
            if ($field == $value) {
                $found = true;
            }
        }

        return $found;
    }
}
