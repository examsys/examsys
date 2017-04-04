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
 * IMS Enterprise plugin settings.
 *
 * @package    plugins_IMS
 * @copyright  2015 onwards, University of Nottingham
 * @author     Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk>
 */

namespace plugins\ims;

/**
 * Get and edit IMS enterprise settings
 *
 * @package    plugins_IMS
 * @copyright  2015 onwards, University of Nottingham
 * @author     Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk>
 */
class ims_enterprise_settings {

  /**
   * Get IMS settings
   *
   * @return stdClass Object containing IMS settings as properties and property values
   */
  public function get_ims_settings($mysqli) {
    $configObject = \Config::get_instance();
    $configObject->set_db_object($mysqli);
    $configObject->load_settings('plugin_ims');
    $settings = (object) $configObject->get_setting('plugin_ims');

    if (property_exists($settings, 'filelocation')) {
      return $settings;
    }
    $defaultsettings = $this->get_default_settings();
    return $defaultsettings;
  }

  /**
   * Save IMS settings
   */
  public function save_ims_settings() {

    if (isset($_POST['submit'])) {
        $this->delete_settings();
        $this->insert_settings();
    }
  }

  /**
   * Update settings
   * @return boolean True if successful, otherwise False
   */
  protected function insert_settings() {
    global $mysqli;

    if ($mysqli->connect_error) {
        die('System Error');
    }

    $settings = array();

    $settings['filelocation'] = check_var('filelocation', 'post', false, false, true);
    $settings['createusers'] = check_var('createusers', 'post', false, false, true);
    $settings['deleteusers'] = check_var('deleteusers', 'post', false, false, true);
    $settings['fixcaseusernames'] = check_var('fixcaseusernames', 'post', false, false, true);
    $settings['fixcasenames'] = check_var('fixcasenames', 'post', false, false, true);
    $settings['sourcedidfailback'] = check_var('sourcedidfailback', 'post', false, false, true);
    $settings['rolemap01'] = check_var('rolemap01', 'post', false, false, true);
    $settings['rolemap02'] = check_var('rolemap02', 'post', false, false, true);
    $settings['rolemap03'] = check_var('rolemap03', 'post', false, false, true);
    $settings['rolemap04'] = check_var('rolemap04', 'post', false, false, true);
    $settings['rolemap05'] = check_var('rolemap05', 'post', false, false, true);
    $settings['rolemap06'] = check_var('rolemap06', 'post', false, false, true);
    $settings['rolemap07'] = check_var('rolemap07', 'post', false, false, true);
    $settings['rolemap08'] = check_var('rolemap08', 'post', false, false, true);
    $settings['truncatemodulecodes'] = check_var('truncatemodulecodes', 'post', false, false, true);
    $settings['createmodules'] = check_var('createmodules', 'post', false, false, true);
    $settings['unenrol'] = check_var('unenrol', 'post', false, false, true);
    $settings['mapmoduleid'] = check_var('mapmoduleid', 'post', false, false, true);
    $settings['mapfullname'] = check_var('mapfullname', 'post', false, false, true);
    $settings['restricttarget'] = check_var('restricttarget', 'post', false, false, true);
    $settings['capitafix'] = check_var('capitafix', 'post', false, false, true);
    $settings['createschools'] = check_var('createschools', 'post', false, false, true);
    $settings['createprogrammes'] = check_var('createprogrammes', 'post', false, false, true);
    $settings['createfaculties'] = check_var('createfaculties', 'post', false, false, true);
    $settings['schoolsource'] = check_var('schoolsource', 'post', false, false, true);
    $settings['programmesource'] = check_var('programmesource', 'post', false, false, true);
    $settings['facultysource'] = check_var('facultysource', 'post', false, false, true);
    $settings['validatexml'] = check_var('validatexml', 'post', false, false, true);

    if (isset($_POST['submit'])) {
      $component = 'plugin_ims';
      // Edit IMS Settings.
      foreach ($settings as $setting => $value) {
        $result = $mysqli->prepare("INSERT INTO config (component, setting, value) VALUE (?, ?, ?)");
        $result->bind_param('sss', $component, $setting, $value);
        $result->execute();
        $result->close();
      }
    }
  }

  /**
   * Insert a IMS settings row (Should be updated immediately by update_settings()
   * @return boolean
   */
  private function delete_settings() {
    global $mysqli;

    $result = $mysqli->prepare("DELETE FROM config WHERE component = 'plugin_ims'");
    $result->execute();
    $result->close();
  }

  /**
   * Save cron run details
   */
  public function save_cron_run() {
    global $mysqli;
    $prevtime = check_var('prevtime', 'post', false, false, true);
    $prevpath = check_var('prevpath', 'post', false, false, true);
    $prevmd5 = check_var('prevmd5', 'post', false, false, true);

    $cron = array();
    $cron['prevtime'] = $prevtime;
    $cron['prevpath'] = $prevpath;
    $cron['prevmd5'] = $prevmd5;
      
    // Edit IMS Settings.
    $result = $mysqli->prepare("UPDATE ims_settings SET
        prevtime = ?,
        prevpath = ?,
        prevmd5 = ?
      WHERE component = 'plugin_ims' AND setting = ");
    $result->bind_param('sss', $prevtime, $prevpath, $prevmd5);

    if ($result->execute()) {
      $result->close();
    }
  }

  /**
   * Get course tag options
   * @return array
   */
  public function get_course_tags() {
    $tags = array();
    $tags['short'] = 'description/short';
    $tags['long'] = 'description/long';
    $tags['sourcedid'] = 'sourcedid/id';
    $tags['coursecode'] = 'extension/course/code';
    return $tags;
  }

  /**
   * Get hierarchy creation options
   * @return array
   */
  public function get_hierarchy_creation_options() {
    global $string;
    $options = array();
    $options['orgname'] = $string['orgname'];
    $options['orgunit'] = $string['orgunit'];
    $options['relationship'] = $string['relationship'];
    return $options;
  }

  /**
   * Get default settings
   * @return \stdClass Object containing IMS settings as properties
   */
  public function get_default_settings() {
    $ims = new \stdClass();
    $ims->filelocation = '';
    $ims->createusers = 1;
    $ims->deleteusers = 1;
    $ims->fixcaseusernames = 1;
    $ims->fixcasenames = 1;
    $ims->sourcedidfailback = '0';
    $roles = new ims_enterprise_roles();
    $ims->rolemap01 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_LEARNER);
    $ims->rolemap02 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_INSTRUCTOR);
    $ims->rolemap03 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_CONTENT_DEVELOPER);
    $ims->rolemap04 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_MEMBER);
    $ims->rolemap05 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_MANAGER);
    $ims->rolemap06 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_MENTOR);
    $ims->rolemap07 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_ADMINISTRATOR);
    $ims->rolemap08 = $roles->get_default_rolemapping(ims_enterprise_roles::ROLE_TEACHINGASSISTANT);
    $ims->truncatemodulecodes = 0;
    $ims->createmodules = 1;
    $ims->createschools = 1;
    $ims->unenrol = 1;
    $ims->mapmoduleid = 'short';
    $ims->mapfullname = 'long';
    $ims->restricttarget = '';
    $ims->capitafix = '0';
    $ims->prevtime = '';
    $ims->prevpath = '';
    $ims->prevmd5 = '';
    $ims->schoolsource = ims_enterprise::SOURCE_RELATIONSHIP;
    $ims->programmesource = ims_enterprise::SOURCE_RELATIONSHIP;
    $ims->facultysource = ims_enterprise::SOURCE_RELATIONSHIP;
    $ims->createfaculties = 1;
    $ims->createprogrammes = 1;
    $ims->validatexml = 1;
    return $ims;
  }
}
