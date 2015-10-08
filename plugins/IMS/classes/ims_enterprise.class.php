<?php
// This file is part of Rogō - http://Rogō.org/ based on code originally part of Moodle - http://moodle.org
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
 * IMS Enterprise file enrolment plugin.
 *
 * This plugin lets the user specify an IMS Enterprise file to be processed.
 * The IMS Enterprise file is mainly parsed on a regular cron,
 * but can also be imported via the UI (Admin Settings).
 * @package    enrol_imsenterprise
 * @copyright  2010 Eugene Venter
 * @copyright  2015 onwards, University of Nottingham
 * @author     Eugene Venter - based on code by Dan Stowell
 * @author     Barry Oosthuizen - based on code by Eugene Venter <barry.oosthuizen@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plugins\IMS;

/**
 * IMS Enterprise file enrolment plugin.
 *
 * @copyright  2010 Eugene Venter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_enterprise {

  /** Default school ID if none is specified */
  const DEFAULT_SCHOOLID = 0;
  /**
   * The grouptype value for modules
   */
  const GROUP_MODULE = 'CLASSES';
  /**
   * The grouptype value for terms
   */
  const GROUP_TERM = 'TERM';
  /**
   * @var bool Whether modulecodes should be trucated or not
   */
  protected $truncatemodulecodes;
  /**
   * @var bool Whether to create new modules or not
   */
  protected $createnewmodules;
  /**
   * @var bool Whether to create new schools or not
   */
  protected $createnewschools;

  /**
   * The IMS settings stored in the database
   * @var stdClass 
   */
  protected $ims_settings;

  /**
   * @var stdClass IMS settings
   */
  protected $ims;

  /**
   * @var $logfp resource file pointer for writing log data to.
   */
  protected $logfp;

  /**
   * @var $continueprocessing bool flag to determine if processing should continue.
   */
  protected $continueprocessing;

  /**
   * @var $xmlcache string cache of xml lines.
   */
  protected $xmlcache;

  /**
   * @var stdClass DB Object 
   */
  protected $db;

  /**
   * @var $modulemappings array of mappings between IMS data fields and Rogō module fields.
   */
  protected $modulemappings;

  /**
   * @var $rolemappings array of mappings between IMS roles and Rogō roles.
   */
  protected $rolemappings;

  /**
   * Read in an IMS Enterprise file.
   * Originally designed to handle v1.1 files but should be able to handle earlier types as well, I believe.
   *
   */
  public function process($mysqli) {

    $this->db = $mysqli;

    $settings = new ims_enterprise_settings();
    $this->ims_settings = $settings->get_ims_settings($this->db);
    // Get configs.
    $filename = $this->get_ims_setting('file_location');
    $logtolocation = $this->get_ims_setting('logto_location');
    $prevtime = $this->get_ims_setting('prev_time');
    $prevmd5 = $this->get_ims_setting('prev_md5');
    $prevpath = $this->get_ims_setting('prev_path');

    $this->logfp = false;
    if (!empty($logtolocation)) {
      $this->logfp = fopen($logtolocation, 'a');
    }

    $fileisnew = false;
    if (file_exists($filename)) {
      $starttime = time();

      $this->log_line('----------------------------------------------------------------------');
      $this->log_line("IMS Enterprise enrol cron process launched at " . date('D, d M Y H:i:s', time()));
      $this->log_line('Found file ' . $filename);
      $this->xmlcache = '';

      // Make sure we understand how to map the IMS-E roles to Moodle roles.
      $this->load_role_mappings();
      // Make sure we understand how to map the IMS-E module names to Moodle module names.
      $this->load_module_mappings();

      $md5 = md5_file($filename); // NB We'll write this value back to the database at the end of the cron.
      $filemtime = filemtime($filename);

      // Decide if we want to process the file (based on filepath, modification time, and MD5 hash)
      // This is so we avoid wasting the server's efforts processing a file unnecessarily.
      if (empty($prevpath) || ($filename != $prevpath)) {
        $fileisnew = true;
        $this->log_line('File is new.  Starting to process it!');
      } else if (isset($prevtime) && ($filemtime <= $prevtime)) {
        $this->log_line('File modification time is not more recent than last update - skipping processing.');
      } else if (isset($prevmd5) && ($md5 == $prevmd5)) {
        $this->log_line('File MD5 hash is same as on last update - skipping processing.');
      } else {
        $this->log_line('File is new.  Starting to process it!');
        $fileisnew = true;
      }

      if ($fileisnew) {
        $xml = new \XMLReader();
        $xml->open($filename);
        $person = 0;
        $group = 0;
        $membership = 0;

        while ($xml->read()) {
          if ($xml->name === 'person' && $xml->nodeType === \XMLReader::ELEMENT) {
            $this->process_person_tag($xml->expand(), $xml->readOuterXml());
          }
          if ($xml->name === 'group' && $xml->nodeType === \XMLReader::ELEMENT) {
            $this->process_group_tag($xml->expand());
          }
          if ($xml->name === 'membership' && $xml->nodeType === \XMLReader::ELEMENT) {
            $this->process_membership_tag($xml->expand());
          }
        }
        $timeelapsed = time() - $starttime;
        $this->log_line('Process has completed. Time taken: ' . $timeelapsed . ' seconds.');
      }
      // These variables are stored so we can compare them against the IMS file, next time round.
      $this->set_prev_configs($filemtime, $filename, $md5);
    } else {
      $this->log_line('File not found: ' . $filename);
    }

    if ($this->logfp) {
      fclose($this->logfp);
    }
  }

  /**
   * Get the group type of a group node
   * @param DOMNode $node
   * @return boolean|string
   */
  protected function get_group_type($node) {
    if (property_exists($node, 'grouptype')) {
      $grouptype = (string) $node->grouptype->typevalue;
      return $grouptype;
    }
    return false;
  }

  /**
   * Process the group tag. This defines a Rogō module.
   *
   * @param string $domnode The raw contents of the XML element
   */
  protected function process_group_tag($domnode) {

    $node = $this->get_xml_element($domnode);
    $grouptype = $this->get_group_type($node);
    if ($grouptype <> self::GROUP_MODULE) {
      return;
    }
    // Get configs.
    $this->truncatemodulecodes = $this->get_ims_setting('truncate_coursecodes');
    $this->createnewmodules = $this->get_ims_setting('createnew_coursecodes');
    $this->createnewschools = $this->get_ims_setting('createnew_schools');

    // Process tag contents.
    $group = new \stdClass();
    $group->modulecode = (string) $node->sourcedid->id;
    $group->long = (string) $node->description->long;
    $group->short = (string) $node->description->short;
    $group->full = (string) $node->description->full;
    $faculty = (string) $node->org->orgname;
    $group->startdate = substr((string) $node->timeframe->begin, -5, 5);

    if (!empty($faculty) && !$facultyid = \FacultyUtils::facultyid_by_name($faculty, $this->db)) {
      if ($this->createnewschools) {
        $facultyid = \FacultyUtils::add_faculty($faculty, $this->db);
      } else {
        $group->school = 0;
      }
    }

    if (!empty($facultyid) && !empty($node->org->orgunit)) {
      $school = (string) $node->org->orgunit;
      if ($schoolid = \SchoolUtils::get_school_id_by_name($school, $this->db)) {
        $schoolname = \SchoolUtils::get_school_faculty($schoolid, $this->db);
        if ($schoolname === $school) {
          $group->school = $schoolid;
        } else {
            $group->school = $this->create_school($facultyid, $school);
        }
      } else {
        $group->school = $this->create_school($facultyid, $school);
      }
    } else {
      $group->school = 0;
    }

    $recstatus = $node["recstatus"];

    if (empty($group->modulecode)) {
      $this->log_line('Error: Unable to find module code in \'group\' element.');
    } else {
      // First, truncate the module code if desired.
      if (intval($this->truncatemodulecodes) > 0) {
        $group->modulecode = ($this->truncatemodulecodes > 0) ? substr($group->modulecode, 0, intval($this->truncatemodulecodes)) : $group->modulecode;
      }

      $this->create_module($group, $recstatus);
    }
  }

  /**
   * Create a new school in the specified faculty if the IMS settings allow new school creation
   * @param int $facultyid
   * @param string $school
   * @return int School ID
   */
  protected function create_school($facultyid, $school) {
    if ($this->createnewschools) {
      $school = \SchoolUtils::add_school($facultyid, $school, $this->db);
    } else {
      $school = 0;
    }
    return $school;
  }

  /**
   * Create a new module or update it if it already exists
   * @param stdClass $group
   * @param int $recstatus
   * @return int|void Return moduleid if module was created or updated.  Return void if the module was deleted.
   */
  protected function create_module($group, $recstatus) {
    $active = 1;
    $selfenroll = 0;
    $neg_marking = 0;
    $peer = true;
    $external = true;
    $stdset = true;
    $mapping = true;
    $map_level = 0;
    $vle_api = '';
    $sms_api = $this->get_ims_setting('file_location');
    $sms_import = 1;
    $timed_exams = 1;
    $exam_q_feedback = 1;
    $add_team_members = 1;
    $schoolid = $group->school;
    $fullname = $group->full;
    $academic_year_start = substr($group->startdate, 0, 2) . '/' . substr($group->startdate, -2, 2);
    $ebel_grid_template = 0;
    $modulecode = $group->modulecode;

    switch ($recstatus) {

      case 3:
        $moduleid = \module_utils::get_idMod($modulecode, $this->db);
        if ($moduleid) {
          \module_utils::delete_module($moduleid, $this->db);
          $this->log_line('Deleted module: ' . $modulecode);
        }
        break;
      case 1:
      case 2:
      default:
        $moduleid = \module_utils::get_idMod($modulecode, $this->db);
        if (!$moduleid) {
          $moduleid = \module_utils::add_modules($modulecode, $fullname, $active, $schoolid, $vle_api, $sms_api, $selfenroll,
              $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $this->db, $sms_import, $timed_exams,
              $exam_q_feedback, $add_team_members, $map_level, $academic_year_start);
          $this->log_line('Created new modulecode: ' . $modulecode);
          return $moduleid;
        }
        $update = array();
        $update['moduleid'] = $modulecode;
        $update['fullname'] = $fullname;
        $update['active'] = $active;
        $update['vle_api'] = $vle_api;
        $update['checklist'] = $vle_api;
        $update['sms'] = $sms_api;
        $update['selfenroll'] = $selfenroll;
        $update['schoolid'] = $schoolid;
        $update['neg_marking'] = $neg_marking;
        $update['ebel_grid_template'] = $ebel_grid_template;
        $update['timed_exams'] = $timed_exams;
        $update['exam_q_feedback'] = $exam_q_feedback;
        $update['add_team_members'] = $add_team_members;
        $update['map_level'] = 0;
        $update['academic_year_start'] = $academic_year_start;

        $updated = \module_utils::update_module_by_code($moduleid, $update, $this->db);
        $this->log_line('Updated module: ' . $modulecode);
        return $updated;

    }
  }

  /**
   * Get a SimleXML element from a DOMNode
   * @param DOMNode $domnode
   * @return SimpleXMLElement
   */
  protected function get_xml_element($domnode) {
    $doc = new \DOMDocument('1.0', 'UTF-8');
    $node = simplexml_import_dom($doc->importNode($domnode, true));
    return $node;
  }

  /**
   * Get DOMNodelist via xpath query
   * @param string $xml
   * @param string $path
   * @return DOMNodelist
   */
  protected function get_xpath_nodelist($xml, $path) {
    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->loadXML($xml);
    $xpath = new \DOMXPath($doc);
    $nodelist = $xpath->query("$path");
    return $nodelist;
  }

  /**
   * Get the initials from an IMS person node
   * @param string $xml
   * @return string
   */
  protected function get_person_initials($xml) {
    $path = "/person/name/n/partname[@partnametype='Initials']";
    return $this->get_nodelist_value($xml, $path);
  }
  
  /**
   * Get the username from an IMS person node
   * @param string $xml
   * @return string
   */
  protected function get_person_username($xml) {
    $path = "/person/userid[@useridtype='username']";
    $username = $this->get_nodelist_value($xml, $path);
    if (!empty($username)) {
      return $username;
    }
    $path = "/person/userid";
    $username = $this->get_nodelist_value($xml, $path);
    if (!empty($username)) {
      return $username;
    }
    return '';
  }
  
  /**
   * Get the Student ID from an IMS person node
   * @param string $xml
   * @return string
   */
  protected function get_person_studentid($xml) {
    $path = "/person/userid[@useridtype='StudentId']";
    return $this->get_nodelist_value($xml, $path);
  }
  
  /**
   * Get the first value in a node list
   * @param string $xml
   * @param path $path
   * @return boolean|string
   */
  protected function get_nodelist_value($xml, $path) {
    $nodelist = $this->get_xpath_nodelist($xml, $path);
    if (!empty($nodelist->length)) {
      return $nodelist->item(0)->nodeValue;
    }
    return false;
  }
  
  /**
   * Get a person's gender based on the gender node
   * @param int $value
   * @return string Gender
   */
  protected function get_person_gender($value) {
    switch ($value) {
      case 1:
        $gender = 'Female';
        break;
      case 2:
        $gender = 'Male';
        break;
      default:
        $gender = 'Unknown';
    }
    return $gender;
  }

  /**
   * Process the person tag. This defines a Rogō user.
   *
   * @param string $domnode The raw contents of the XML element
   */
  protected function process_person_tag($domnode, $xml) {
    $node = $this->get_xml_element($domnode);

    // Get plugin configs.
    $sourcedidfailback = $this->get_ims_setting('sourcedid_failback');
    $fixcaseusernames = $this->get_ims_setting('fixcase_usernames');
    $fixcasepersonalnames = $this->get_ims_setting('fixcase_names');
    $imsdeleteusers = $this->get_ims_setting('delete_users');
    $createnewusers = $this->get_ims_setting('create_users');

    $person = new \stdClass();
    $person->idnumber = (string) $node->sourcedid->id;
    $person->firstname = (string) $node->name->n->given;
    $person->surname = (string) $node->name->n->family;
    $person->initials = $this->get_person_initials($xml);
    $person->title = (string) $node->name->n->prefix;
    $gender = (int) $node->demographics->gender;
    $person->gender = $this->get_person_gender($gender);
    if ($sourcedidfailback) {
      $person->username = (string) $node->sourcedid->id;
    } else {
      $person->username = $this->get_person_username($xml);
    }

    $person->email = (string) $node->email;
    $person->full = (string) $node->description->full;
    $person->school = (string) $node->org->orgunit;
    $person->faculty = (string) $node->org->orgname;
    $systemrole = (string) $node->systemrole['systemroletype']; //TODO decide what to do with this (if anything).
    $person->role = strtolower((string) $node->institutionrole["institutionroletype"]);
    $person->startdate = substr((string) $node->timeframe->begin, -5, 5);
    $person->grade = (string) $node->extension->course;
    if ($fixcaseusernames && isset($person->username)) {
      $person->username = strtolower($person->username);
    }
    if ($fixcasepersonalnames) {
      if (isset($person->firstname)) {
        $person->firstname = ucwords(strtolower($person->firstname));
      }
      if (isset($person->surname)) {
        $person->surname = ucwords(strtolower($person->surname));
      }
    }

    // Fix case of some of the fields if required.
    if ($fixcaseusernames && isset($person->username)) {
      $person->username = strtolower($person->username);
    }
    if ($fixcasepersonalnames) {
      if (isset($person->firstname)) {
        $person->firstname = ucwords(strtolower($person->firstname));
      }
      if (isset($person->surname)) {
        $person->surname = ucwords(strtolower($person->surname));
      }
    }

    $recstatus = $node['recstatus'];

    // Now if the recstatus is 3, we should delete the user if-and-only-if the setting for delete users is turned on.
    if ($recstatus == 3) {
      if ($imsdeleteusers) { // If we're allowed to delete user records.
        $this->delete_user($person);
      } else {
        $this->log_line("Ignoring deletion request for user '$person->username' (ID number $person->idnumber).");
      }
    } else { // Add or update record.
      // If the user exists (matching sourcedid) then we don't need to do anything.
      if (!$userid = \UserUtils::username_exists($person->username, $this->db) && $createnewusers) {
        // If they don't exist and haven't a defined username, we log this as a potential problem.
        if ((!isset($person->username)) || (strlen($person->username) == 0)) {
          $this->log_line("Cannot create new user for ID # $person->idnumber" .
              "- no username listed in IMS data for this person.");
        } else {
          // If they don't exist and they have a defined username, and $createnewusers == true, we create them.
          $userid = \UserUtils::create_user($person->username, '', $person->title, $person->firstname, $person->surname, $person->email,
              $person->grade, $person->gender, '', $person->role, $person->idnumber, $this->db, $person->initials);
          $this->log_line("Created user record (' . $userid . ') for user '$person->username' (ID number $person->idnumber).");
        }
      } else if ($createnewusers) {
        $this->log_line("User record already exists for user '$person->username' (ID number $person->idnumber).");

        // It is totally wrong to mess with deleted users flag directly in database!!!
        // There is no official way to undelete user, sorry..
      } else {
        $this->log_line("No user record found for '$person->username' (ID number $person->idnumber).");
      }
    }
  }

  /**
   * Delete a user
   * @param stdClass $person
   */
  protected function delete_user($person) {
      if ($userid = \UserUtils::username_exists($person->username, $this->db)) {
        try {
          \UserUtils::delete_userID($userid);
          $this->log_line("Deleted user '$person->username' (ID number $person->idnumber).");
        } catch (Exception $ex) {
          $this->log_line("Error deleting '$person->username' (ID number $person->idnumber).");
        }
      } else {
        $this->log_line("Can not delete user '$person->username' (ID number $person->idnumber) - user does not exist.");
      }
  }

  function update_module_enrolement($module, $idMod, $sms_api, $mysqli = 'NOTSET', $session = 'NOTSET') {
    return true;
  }
  /**
   * Process the membership tag. This defines whether the specified Rogō users
   * should be added/removed as teachers/students.
   *
   * @param string $domnode The raw contents of the XML element
   */
  protected function process_membership_tag($domnode) {
    $node = $this->get_xml_element($domnode);
    // Get plugin configs.
    $truncatemodulecodes = $this->get_ims_setting('truncate_coursecodes');
    $imscapitafix = $this->get_ims_setting('capitafix'); //TODO decide what to do with this. (Copied from Moodle code)

    $modulecode = (string) $node->sourcedid->id;
    $members = $node->member;
    foreach ($members as $membertag => $member) {
      $username = (string) $member->role->userid;
      $roletype = (string) $member->role['roletype'];
      $idnumber = (string) $member->sourcedid->id;
      $status = (string) $member->role->status;
      $userid = \UserUtils::username_exists($username, $this->db);
      $start_date = substr((string) $member->role->timeframe->begin, -10, 4);
      $success = \UserUtils::add_student_to_module_by_name($userid, $modulecode, 1, $start_date, $this->db);
    }
  }

  /**
   * Display logging information.
   *
   * @param string $string Text to write (newline will be added automatically)
   */
  protected function log_line($string) {
    echo $string . "\n";
  }

  /**
   * Process the INNER contents of a <timeframe> tag, to return beginning/ending dates.
   *
   * @param string $string tag to decode.
   * @return stdClass beginning and/or ending is returned, in unix time, zero indicating not specified.
   */
  protected static function decode_timeframe($string) {
    $ret = new \stdClass();
    $ret->begin = $ret->end = 0;
    // Explanatory note: The matching will ONLY match if the attribute restrict="1"
    // because otherwise the time markers should be ignored (participation should be
    // allowed outside the period).
    if (preg_match('{<begin\s+restrict="1">(\d\d\d\d)-(\d\d)-(\d\d)</begin>}is', $string, $matches)) {
      $ret->begin = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    }
    if (preg_match('{<end\s+restrict="1">(\d\d\d\d)-(\d\d)-(\d\d)</end>}is', $string, $matches)) {
      $ret->end = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    }
    return $ret;
  }

  /**
   * Load the role mappings (from the config), so we can easily refer to
   * how an IMS-E role corresponds to a Rogō role
   */
  protected function load_role_mappings() {

    $imsroles = new ims_enterprise_roles();
    $imsroles = $imsroles->get_imsroles();

    $this->rolemappings = array();
    foreach ($imsroles as $imsrolenum => $imsrolename) {
      $this->rolemappings[$imsrolenum] = $this->rolemappings[$imsrolename] = $this->get_ims_setting('rolemap' . $imsrolenum);
    }
  }

  /**
   * Load the name mappings (from the config), so we can easily refer to
   * how an IMS-E module properties corresponds to a Rogō module properties
   */
  protected function load_module_mappings() {

    $imsnames = new ims_enterprise_modules();
    $moduleattrs = $imsnames->get_moduleattrs();

    $this->modulemappings = array();
    foreach ($moduleattrs as $moduleattr) {
      $this->modulemappings[$moduleattr] = $this->get_ims_setting('map' . $moduleattr);
    }
  }

  /**
   * Get a particular IMS setting (as stored in the database)
   * @param string $property
   * @return string
   */
  protected function get_ims_setting($property) {
    return $this->ims_settings->{$property};
  }

  /**
   * Set configuration options
   * @param array $configs
   */
  
  /**
   * 
   * @param int $prev_time
   * @param string $prev_path
   * @param string $prev_md5
   */
  public function set_prev_configs($prev_time, $prev_path, $prev_md5) {

    // Edit IMS Settings.
    $result = $this->db->prepare("UPDATE ims_settings SET prev_time = ?, prev_path = ?, prev_md5 = ? WHERE id = 1");
    $result->bind_param("iss", $prev_time, $prev_path, $prev_md5);

    if ($result->execute()) {
      $result->close();
    }
  }

}
