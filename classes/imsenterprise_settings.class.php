<?php
// This file is part of Rogō - http://Rogō.org/
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
 * Get and edit IMS enterprise settings
 */
class imsenterprise_settings {

  /**
   * Get IMS settings
   *
   * @return stdClass Object containing IMS settings as properties and property values
   */
  public function get_ims_settings($mysqli) {

    $result = $mysqli->prepare("SELECT
                                    file_location,
                                    logto_location,
                                    create_users,
                                    delete_users,
                                    fixcase_usernames,
                                    fixcase_names,
                                    sourcedid_failback,
                                    rolemap01,
                                    rolemap02,
                                    rolemap03,
                                    rolemap04,
                                    rolemap05,
                                    rolemap06,
                                    rolemap07,
                                    rolemap08,
                                    truncate_coursecodes,
                                    createnew_coursecodes,
                                    createnew_categories,
                                    unenrol,
                                    mapshortname,
                                    mapfullname,
                                    mapsummary,
                                    restricttarget,
                                    capitafix,
                                    prev_time,
                                    prev_path,
                                    prev_md5
                                FROM
                                    ims_settings
                                WHERE
                                    id = 1");

    $result->bind_result(
        $ims['file_location'], $ims['logto_location'], $ims['create_users'], $ims['delete_users'], $ims['fixcase_usernames'],
        $ims['fixcase_names'], $ims['sourcedid_failback'], $ims['rolemap01'], $ims['rolemap02'], $ims['rolemap03'],
        $ims['rolemap04'], $ims['rolemap05'], $ims['rolemap06'], $ims['rolemap07'], $ims['rolemap08'], $ims['truncate_coursecodes'],
        $ims['createnew_coursecodes'], $ims['createnew_categories'], $ims['unenrol'], $ims['mapshortname'], $ims['mapfullname'],
        $ims['mapsummary'], $ims['restricttarget'], $ims['capitafix'], $ims['prev_time'], $ims['prev_path'], $ims['prev_md5']);

    $result->execute();

    while ($result->fetch()) {
      $ims = (object) $ims;
    }

    return $ims;
  }

  public function save_ims_settings() {
    global $mysqli;
    $imsfilelocation = check_var('imsfilelocation', 'post', false, false, true);
    $logtolocation = check_var('logtolocation', 'post', false, false, true);
    $createnewusers = check_var('createnewusers', 'post', false, false, true);
    $imsdeleteusers = check_var('imsdeleteusers', 'post', false, false, true);
    $fixcaseusernames = check_var('fixcaseusernames', 'post', false, false, true);
    $fixcasepersonalnames = check_var('fixcasepersonalnames', 'post', false, false, true);
    $sourcedidfailback = check_var('imssourcedidfallback', 'post', false, false, true);
    $imsrolemap01 = check_var('imsrolemap01', 'post', false, false, true);
    $imsrolemap02 = check_var('imsrolemap02', 'post', false, false, true);
    $imsrolemap03 = check_var('imsrolemap03', 'post', false, false, true);
    $imsrolemap04 = check_var('imsrolemap04', 'post', false, false, true);
    $imsrolemap05 = check_var('imsrolemap05', 'post', false, false, true);
    $imsrolemap06 = check_var('imsrolemap06', 'post', false, false, true);
    $imsrolemap07 = check_var('imsrolemap07', 'post', false, false, true);
    $imsrolemap08 = check_var('imsrolemap08', 'post', false, false, true);
    $truncatecoursecodes = check_var('truncatecoursecodes', 'post', false, false, true);
    $createnewcourses = check_var('createnewcourses', 'post', false, false, true);
    $imsunenrol = check_var('imsunenrol', 'post', false, false, true);
    $imscoursemapshortname = check_var('imscoursemapshortname', 'post', false, false, true);
    $imscoursemapfullname = check_var('imscoursemapfullname', 'post', false, false, true);
    $imscoursemapsummary = check_var('imscoursemapsummary', 'post', false, false, true);
    $imsrestricttarget = check_var('imsrestricttarget', 'post', false, false, true);
    $imscapitafix = check_var('imscapitafix', 'post', false, false, true);
    $create_newcategories = check_var('createnewcategories', 'post', false, false, true);

    if (isset($_POST['submit'])) {
      // Edit IMS Settings.
      $result = $mysqli->prepare("UPDATE ims_settings SET
      file_location = ?,
      logto_location = ?,
      create_users = ?,
      delete_users = ?,
      fixcase_usernames = ?,
      fixcase_names = ?,
      sourcedid_failback = ?,
      rolemap01 = ?,
      rolemap02 = ?,
      rolemap03 = ?,
      rolemap04 = ?,
      rolemap05 = ?,
      rolemap06 = ?,
      rolemap07 = ?,
      rolemap08 = ?,
      truncate_coursecodes = ?,
      createnew_coursecodes = ?,
      unenrol = ?,
      mapshortname = ?,
      mapfullname = ?,
      mapsummary = ?,
      restricttarget = ?,
      capitafix = ?,
      createnew_categories = ?
    WHERE id = 1");
      $result->bind_param('ssiiiiissssssssiiissssii', $imsfilelocation, $logtolocation, $createnewusers, $imsdeleteusers,
          $fixcaseusernames, $fixcasepersonalnames, $sourcedidfailback, $imsrolemap01, $imsrolemap02, $imsrolemap03, $imsrolemap04,
          $imsrolemap05, $imsrolemap06, $imsrolemap07, $imsrolemap08, $truncatecoursecodes, $createnewcourses, $imsunenrol,
          $imscoursemapshortname, $imscoursemapfullname, $imscoursemapsummary, $imsrestricttarget, $imscapitafix,
          $create_newcategories
      );

      if ($result->execute()) {
        $result->close();
      }
    }
  }

  public function save_cron_run() {
    global $mysqli;
    $prevtime = check_var('prev_time', 'post', false, false, true);
    $prevpath = check_var('prev_path', 'post', false, false, true);
    $prevmd5 = check_var('prev_md5', 'post', false, false, true);

    if (isset($_POST['submit'])) {
      // Edit IMS Settings.
      $result = $mysqli->prepare("UPDATE ims_settings SET
      prev_time = ?,
      prev_path = ?,
      prev_md5 = ?
    WHERE id = 1");
      $result->bind_param('iss', $prevtime, $prevpath, $prevmd5
      );

      if ($result->execute()) {
        $result->close();
      }
    }
  }

  /**
   *
   * @return array
   */
  public function get_role_mappings() {
    global $string;

    $ignore = $string['ignore'];
    $sysadmin = $string['sysadmin'];
    $staff = $string['staff'];
    $student = $string['student'];
    $invigilator = $string['invigilator'];
    $inactive = $string['inactive'];
    $suspended = $string['suspended'];
    $nagios = $string['nagios'];

    $rolemappings = array();
    $rolemappings[0] = $ignore;
    $rolemappings[$student] = $student;
    $rolemappings[$staff] = $staff;
    $rolemappings[$sysadmin] = $sysadmin;
    $rolemappings[$invigilator] = $invigilator;
    $rolemappings[$inactive] = $inactive;
    $rolemappings[$nagios] = $nagios;
    $rolemappings[$suspended] = $suspended;
    return $rolemappings;
  }

/**
   *
   * @global type $string
   * @return type
   */
  public function get_course_tags() {
    global $string;
    $tags = array();
    $tags['ignore'] = $string['emptyattribute'];
    $tags['short'] = $string['short'];
    $tags['long'] = $string['long'];
    $tags['full'] = $string['full'];
    $tags['coursecode'] = $string['coursecode'];
    return $tags;
  }

}
