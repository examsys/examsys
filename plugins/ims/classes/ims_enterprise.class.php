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
 * @package    plugins_IMS
 * @copyright  2010 Eugene Venter
 * @copyright  2015 onwards, University of Nottingham
 * @author     Eugene Venter - based on code by Dan Stowell
 * @author     Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk> - based on code by Eugene Venter
 */

namespace plugins\ims;
use plugins\ims\ims_enterprise_roles;

/**
 * IMS Enterprise file enrolment plugin.
 *
 * @copyright  2010 Eugene Venter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_enterprise {
  /** The orgname tag */
  const SOURCE_ORGNAME = 'orgname';
  /** The orgunit tag */
  const SOURCE_ORGUNIT = 'orgunit';
  /** The relationship tag */
  const SOURCE_RELATIONSHIP = 'relationship';
  /** Active role status */
  const ROLE_STATUS_ACTIVE = 1;
  /** Inactive role status */
  const ROLE_STATUS_INACTIVE = 0;
  /** Default school ID if none is specified */
  const DEFAULT_SCHOOLID = 0;
  /**  The grouptype value for modules */
  const GROUP_MODULE = 'CLASSES';
  /** The grouptype value for terms */
  const GROUP_TERM = 'TERM';
  /** The grouptype value for courses */
  const GROUP_PROGRAMME = 'PROGRAMME';
  /** The grouptype value for faculties */
  const GROUP_FACULTY = 'FACULTY';
  /** The grouptype value for schools */
  const GROUP_SCHOOL = 'SCHOOL';
  /** The recstatus to delete a record */
  const RECORD_DELETE = 3;
  /** The recstatus to create a record */
  const RECORD_CREATE = 1;
  /** The recstatus to update a record */
  const RECORD_UPDATE = 2;
  /** The recstatus to update a record */
  const RECORD_UNDEFINED = -1;
  /** @var bool Whether modulecodes should be trucated or not */
  protected $truncatemodulecodes;
  /** @var bool Whether to create new modules or not */
  protected $createmodules;
  /** @var bool Whether to create new schools or not */
  protected $createschools;
  /** @var bool Whether to create new programmes or not */
  protected $createprogrammes;
  /** @var bool Whether to create new faculties or not */
  protected $createfaculties;
  /**  @var stdClass @var stdClass */
  protected $ims_settings;
  /** @var stdClass IMS settings */
  protected $ims;
  /** @var $logfp resource file pointer for writing log data to. */
  protected $logfp;
  /** @var $continueprocessing bool flag to determine if processing should continue. */
  protected $continueprocessing;
  /** @var $validatexml bool Validate XML against local DTD. */
  protected $validatexml;
  /** @var stdClass DB Object */
  protected $db;
  /** @var $modulemappings array of mappings between IMS data fields and Rogō module fields. */
  protected $modulemappings;
  /** @var $rolemappings array of mappings between IMS roles and Rogō roles. */
  protected $rolemappings;
  /** @var string Xml file location */
  protected $filename;
  /** @var array A cache of group relationships */
  protected $grouprelationships = array();
  /** $var string The node to get the moduleid from */
  protected $mapmoduleid;
  /** $var string The node to get the module full name from */
  protected $mapfullname;
  /** $var string||null Only process data if a target is specified */
  protected $restricttarget;
  /**
   * Constructor
   * @param stdClass $mysqli
   */
  public function __construct($mysqli) {
    $this->db = $mysqli;
    $settings = new ims_enterprise_settings();
    $this->ims_settings = $settings->get_ims_settings($this->db);
    // Get configs.
    $this->filename = $this->get_ims_setting('filelocation');
    $this->validatexml = $this->get_ims_setting('validatexml');
    $this->createfaculties = $this->get_ims_setting('createfaculties');
    $this->createschools = $this->get_ims_setting('createschools');
    $this->schoolsource = $this->get_ims_setting('schoolsource');
    $this->facultysource = $this->get_ims_setting('facultysource');
    $this->programmesource = $this->get_ims_setting('programmesource');
    $this->createfaculties = $this->get_ims_setting('createfaculties');
    $this->prevtime = $this->get_ims_setting('prevtime');
    $this->prevmd5 = $this->get_ims_setting('prevmd5');
    $this->prevpath = $this->get_ims_setting('prevpath');
    $this->createprogrammes = $this->get_ims_setting('createprogrammes');
    $this->createfaculties = $this->get_ims_setting('createfaculties');
    $this->schoolsource = $this->get_ims_setting('schoolsource');
    $this->truncatemodulecodes = $this->get_ims_setting('truncatemodulecodes');
    $this->createmodules = $this->get_ims_setting('createmodules');
    $this->createschools = $this->get_ims_setting('createschools');
    $this->sourcedidfailback = $this->get_ims_setting('sourcedidfailback');
    $this->fixcaseusernames = $this->get_ims_setting('fixcaseusernames');
    $this->fixcasenames = $this->get_ims_setting('fixcasenames');
    $this->deleteusers = $this->get_ims_setting('deleteusers');
    $this->createusers = $this->get_ims_setting('createusers');
    $this->truncatemodulecodes = $this->get_ims_setting('truncatemodulecodes');
    $this->capitafix = $this->get_ims_setting('capitafix');
    $this->mapmoduleid = $this->get_ims_setting('mapmoduleid');
    $this->mapfullname = $this->get_ims_setting('mapfullname');
    $this->restricttarget = $this->get_ims_setting('restricttarget');
  }

  /**
   * Read in an IMS Enterprise file.
   */
  public function process() {
    $fileisnew = false;
    if (empty($this->filename)) {
        $this->log_line('IMS Enterprise config settings have not been configured.  Please go to Administrative Tools > IMS Settings to configure the settings');
    } else if (file_exists($this->filename)) {
      $starttime = microtime(true);

      $this->log_line('----------------------------------------------------------------------');
      $this->log_line("IMS Enterprise enrol cron process launched at " . date('D, d M Y H:i:s', time()));
      $this->log_line('Found file ' . $this->filename);

      $md5 = md5_file($this->filename); // NB We'll write this value back to the database at the end of the cron.
      $filemtime = filemtime($this->filename);

      // Decide if we want to process the file (based on filepath, modification time, and MD5 hash)
      // This is so we avoid wasting the server's efforts processing a file unnecessarily.
      if (empty($this->prevpath) || ($this->filename != $this->prevpath)) {
        $fileisnew = true;
        $this->log_line('File is new.  Starting to process it!');
      } else if (isset($this->prevtime) && ($filemtime <= $this->prevtime)) {
        $this->log_line('File modification time is not more recent than last update - skipping processing.');
      } else if (isset($this->prevmd5) && ($md5 == $this->prevmd5)) {
        $this->log_line('File MD5 hash is same as on last update - skipping processing.');
      } else {
        $this->log_line('File is new.  Starting to process it!');
        $fileisnew = true;
      }
      if ($fileisnew) {
        try {
          $this->process_properties_tag();
          $this->process_group_tags(self::GROUP_FACULTY);
          $this->process_group_tags(self::GROUP_SCHOOL);
          $this->process_group_tags(self::GROUP_PROGRAMME);
          $this->process_group_tags(self::GROUP_MODULE);
          $this->process_persons();
          $this->process_memberships();
        } catch (\Exception $e) {
          $this->log_line("IMS Enterprise XML processing error: " .  $e->getMessage());
        }

        $timeelapsed = microtime(true) - $starttime;
        $this->log_line('Process has completed. Time taken: ' . $timeelapsed . ' seconds.');
        // These variables are stored so we can compare them against the IMS file, next time round.
        $this->set_prev_configs($filemtime, $this->filename, $md5);
      }
    } else {
      $this->log_line('File not found: ' . $this->filename);
    }
  }

  /**
   * Check if IMS Enterprise data has been restricted to a specific target
   *
   * An IMS Enterprise data file could be intended for multiple "targets" - different LMSes, or different systems
   * within a school/university. It\'s possible to specify in the Enterprise file that the data is intended for
   * one or more named target systems, by naming them in <target> tags contained within the <properties> tag
   * @param string $grouptype
   */
  protected function process_properties_tag() {
    if (empty($this->restricttarget)) {
      return true;
    }
    $this->log_line("Processing properties tag");
    $xml = $this->get_xml_reader();
    while ($xml->read()) {
      if ($this->validatexml and !$xml->isValid()) {
        throw new \Exception('Invalid XML');
      }
      if ($xml->name === 'properties' && $xml->nodeType === \XMLReader::ELEMENT) {
        $node = $this->get_xml_element($xml->expand());
        $target = (string) $node->target;
        if ($this->restricttarget === $target) {
          $xml->close();
          return true;
        }
      }
    }
    $xml->close();
    throw new \Exception("Target not found: IMS Settings restrict processing to " . $this->restricttarget . " as target"
        . "\nEither change your IMS settings or configure your LMS to specify the correct target");
  }

  /**
   * Return a new instance of XMLReader based on the IMS file
   * @return \XMLReader
   */
  protected function get_xml_reader() {
    $xml = new \XMLReader();
    $xml->open($this->filename);
    if ($this->validatexml) {
      $xml->setParserProperty(\XMLReader::VALIDATE, true);
    }
    return $xml;
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
   * Process the FACULTY group nodes
   * @param string $faculty
   * @return void|stdClass
   */
  protected function process_group_faculties () {
    $xml = $this->get_xml_reader();
    while ($xml->read()) {
      if ($this->validatexml and !$xml->isValid()) {
        throw new \Exception('Invalid XML');
      }
      if ($xml->name === 'group' && $xml->nodeType === \XMLReader::ELEMENT) {
        $node = $this->get_xml_element($xml->expand());
        if (!is_object($node)) {
          continue;
        }
        $grouptype = $this->get_group_type($node);
        if ($grouptype === self::GROUP_FACULTY) {
          $this->process_group_faculty($node);
        }
      }
    }
    $xml->close();
  }

  /**
   * Get the short description of a node
   * @param string $nodeid
   * @param string $type
   * @return string
   */
  protected function get_node_shortname($nodeid, $type) {
    $nodename = '';
    $xml = $this->get_xml_reader();
    while ($xml->read()) {
      if ($this->validatexml and !$xml->isValid()) {
        throw new \Exception('Invalid XML');
      }
      if ($xml->name === 'group' && $xml->nodeType === \XMLReader::ELEMENT) {
        $node = $this->get_xml_element($xml->expand());
        if (!is_object($node)) {
          continue;
        }
        $grouptype = $this->get_group_type($node);
        if ($grouptype === $type) {
          $sourcedid = (string) $node->sourcedid->id;
          if ($nodeid === $sourcedid) {
            $nodename = (string) $node->description->short;
          }
        }
      }
    }
    $xml->close();
    return $nodename;
  }

  /**
   * Get username based on node id
   * @param string $nodeid
   * @return string
   */
  protected function get_username($nodeid) {
    $username = '';
    $xml = $this->get_xml_reader();
    while ($xml->read()) {
      if ($this->validatexml and !$xml->isValid()) {
        throw new \Exception('Invalid XML');
      }
      if ($xml->name === 'person' && $xml->nodeType === \XMLReader::ELEMENT) {
        $node = $this->get_xml_element($xml->expand());
        if (!is_object($node)) {
          continue;
        }
        $sourcedid = (string) $node->sourcedid->id;
        if ($sourcedid === $nodeid) {
          $username = $this->get_username_from_xml($xml->readOuterXML());
          break;
        }
      }
    }
    $xml->close();
    return $username;
  }

  /**
   * Process group nodes of FACULTY group type.
   * @param stdClass $node
   */
  protected function process_group_faculty($node) {
    $faculty = (string) $node->description->short;
    if ($this->createfaculties && !empty($faculty) && !$facultyid = \FacultyUtils::facultyid_by_name($faculty, $this->db)) {
      if ($this->createfaculties) {
        \FacultyUtils::add_faculty($faculty, $this->db);
      }
    }
  }

  /**
   * Process group nodes of SCHOOL group type.
   * @param stdClass $node
   * @return void
   */
  protected function process_group_school($node) {
    if (!$this->createschools) {
      $this->log_line('The configured IMS settings prevent new schools from being created.  Skipping this process');
      return;
    }
    if ($this->schoolsource === self::SOURCE_ORGNAME || $this->schoolsource === self::SOURCE_ORGUNIT) {
      // We should get the school from the orgname or orgunit in a module so skip this.
      return;
    }

    $schoolid = (string) $node->sourcedid->id;
    $faculty = $this->get_related_node_by_setting($this->facultysource, $node, self::GROUP_FACULTY);

    $this->log_line('Faculty: ' . $faculty);

    if (!empty($faculty) && !$facultyid = \FacultyUtils::facultyid_by_name($faculty, $this->db)) {
      if ($this->createfaculties) {
        $this->log_line('About ot add faculty from line 384');
        $facultyid = \FacultyUtils::add_faculty($faculty, $this->db);
      } else {
        return;
      }
    }

    if (!empty($facultyid)) {
      $school = (string) $node->description->short;
      if ($schoolid = \SchoolUtils::get_school_id_by_name($school, $this->db)) {
        $schoolname = \SchoolUtils::get_school_faculty($schoolid, $this->db);
        if ($schoolname !== $school) {
          $this->create_school($facultyid, $school);
        }
      } else {
        $this->create_school($facultyid, $school);
      }
    }
  }

  /**
   * Process grouptags for a particular group type
   * @param string $grouptype
   */
  protected function process_group_tags($grouptype) {
    $this->log_line("Processing $grouptype tag");
    $xml = $this->get_xml_reader();
    while ($xml->read()) {
      if ($this->validatexml and !$xml->isValid()) {
        throw new \Exception('Invalid XML');
      }
      if ($xml->name === 'group' && $xml->nodeType === \XMLReader::ELEMENT) {
        $node = $this->get_xml_element($xml->expand());
        if (!is_object($node)) {
          continue;
        }
        $thisgrouptype = $this->get_group_type($node);
        if (empty($thisgrouptype)) {
          continue;
        }
        if ($thisgrouptype !== $grouptype) {
          continue;
        }
        switch ($grouptype) {
          case self::GROUP_FACULTY:
            $this->process_group_faculty($node);
            continue;
          case self::GROUP_MODULE:
            $this->process_group_module($node);
            continue;
          case self::GROUP_PROGRAMME:
            $this->process_group_programme($node);
            continue;
          case self::GROUP_SCHOOL:
            $this->process_group_school($node);
            continue;
          default:
            continue;
        }
      }
    }
    $xml->close();
  }

  /**
   * Process group nodes of PROGRAMME group type.
   * @param stdClass $node
   * @return void
   */
  protected function process_group_programme($node) {
    if (!$this->createprogrammes) {
      $this->log_line('The configured IMS settings prevent new programmes from being created.  Skipping this process');
      return;
    }

    if ($this->programmesource === self::SOURCE_ORGNAME || $this->programmesource === self::SOURCE_ORGUNIT) {
      // We should get the programme from the orgname or orgunit in a module so skip this.
      $this->log_line('We should get the programme from the orgname or orgunit in a module so skipping creating programmes from group nodes.');
      return;
    }

    $programmeid = (string) $node->sourcedid->id;
    $school = $this->get_related_node_by_setting($this->schoolsource, $node, self::GROUP_SCHOOL);

    if ((empty($school)) || (!empty($school) && !$schoolid = \SchoolUtils::school_name_exists($school, $this->db))) {
      $this->log_line('The xml file is missing the school for programmes');
      $this->log_line('$schoolsourcedid: ' . $schoolsourcedid);
      return;
    }

    if (!empty($schoolid)) {
      $name = (string) $node->description->short;
      $description = (string) $node->description->long;
      if ($programmeid = \CourseUtils::get_course_details_by_name($name, $this->db)) {
        $programmename = \CourseUtils::get_course_name_by_id($programmeid, $this->db);
        if ($programmename !== $name) {
          $this->log_line('Created programme: ' . $name . ' - ' . $description);
          \CourseUtils::add_course($schoolid, $name, $description, null, null, $this->db);
        }
      } else {
        $this->log_line('Created programme: ' . $name . ' - ' . $description);
        \CourseUtils::add_course($schoolid, $name, $description, null, null, $this->db);
      }
    }
  }

  /**
   * Get related node according to configured IMS setting
   * @param string $source
   * @param stdClass $node
   * @param string $grouptype
   * @return string The node short name
   */
  protected function get_related_node_by_setting($source, $node, $grouptype) {
    switch ($source) {
      case self::SOURCE_ORGNAME:
        $nodename = (string) $node->org->orgname;
        break;
      case self::SOURCE_ORGUNIT:
        $nodename = (string) $node->org->orgunit;
        break;
      case self::SOURCE_RELATIONSHIP:
      default:
        $schoolsourcedid = (string) $node->relationship->sourcedid->id;
        $nodename = $this->get_node_shortname($schoolsourcedid, $grouptype);
    }
    return $nodename;
  }

  /**
   * Process group nodes of CLASSES group type.
   * @param stdClass $node
   * @return void
   */
  protected function process_group_module($node) {
    // Process tag contents.
    $group = new \stdClass();

    switch ($this->mapmoduleid) {
      case 'long':
        $group->modulecode = (string) $node->description->long;
        break;
      case 'short':
        $group->modulecode = (string) $node->description->short;
        break;
      case 'full':
        $group->modulecode = (string) $node->description->full;
        break;
      case 'coursecode':
        $group->modulecode = (string) $node->extension->course->code;
        break;
      case 'sourcedid':
      default:
        $group->modulecode = (string) $node->sourcedid->id;
        break;
    }

    switch ($this->mapfullname) {
      case 'long':
        $group->full = (string) $node->description->long;
        break;
      case 'short':
        $group->full = (string) $node->description->short;
        break;
      case 'coursecode':
        $group->full = (string) $node->extension->course->code;
        break;
      case 'sourcedid':
      default:
        $group->full = (string) $node->sourcedid->id;
        break;
    }

    $group->startdate = substr((string) $node->timeframe->begin, -5, 5);

    $this->log_line('School source: ' . $this->schoolsource);

    $school = $this->get_related_node_by_setting($this->schoolsource, $node, self::GROUP_SCHOOL);
    $schoolid = \SchoolUtils::school_name_exists($school, $this->db);

    $recstatus = $this->detect_recstatus($node);
    if (empty($schoolid) and $recstatus != self::RECORD_DELETE) {
      $this->log_line('The xml file is missing the school for modules');
      return;
    }
    if (!empty($schoolid)) {
      $this->log_line('School ID: ' . $schoolid . ', School Name: ' . $school);
      $group->school = $schoolid;
    }
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
   * Process the group tag. This defines a Rogō module.
   * @param string $domnode The raw contents of the XML element
   */
  protected function process_group_tag($domnode) {

    $node = $this->get_xml_element($domnode);
    $grouptype = $this->get_group_type($node);

    switch ($grouptype) {
      case self::GROUP_PROGRAMME:
        $this->process_group_course($node);
        break;
      case self::GROUP_FACULTY:
        $this->process_group_faculty($node);
        break;
      case self::GROUP_MODULE:
        $this->process_group_module($node);
        break;
      case self::GROUP_SCHOOL:
        $this->process_group_school($node);
        break;
      default;
        continue;
    }
  }

  /**
   * Create a new school in the specified faculty if the IMS settings allow new school creation
   * @param int $facultyid
   * @param string $school
   * @return int|bool School ID if created.  Otherwise, 0 means creation was disabled, and false an error
   */
  protected function create_school($facultyid, $school) {
    if ($this->createschools) {
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
    $sms_api = 'ims_enterprise';
    $sms_import = 0;
    $timed_exams = 1;
    $exam_q_feedback = 1;
    $add_team_members = 1;
    if ($recstatus != self::RECORD_DELETE) {
      $schoolid = $group->school;
    }
    $fullname = $group->full;
    $academic_year_start = substr($group->startdate, 0, 2) . '/' . substr($group->startdate, -2, 2);
    $ebel_grid_template = 0;
    $modulecode = $group->modulecode;

    $this->log_line('About to create module ' . $modulecode);

    switch ($recstatus) {
      case self::RECORD_UNDEFINED:
        break;
      case self::RECORD_DELETE:
        $moduleid = \module_utils::get_idMod($modulecode, $this->db);
        if ($moduleid) {
          if (!\module_utils::module_in_use($moduleid, $this->db)) {
            \module_utils::delete_module($moduleid, $this->db);
            $this->log_line('Deleted module: ' . $modulecode);
          }
        }
        break;
      case self::RECORD_CREATE:
      case self::RECORD_UPDATE:
      default:
        $moduleid = \module_utils::get_idMod($modulecode, $this->db);
        if (!$moduleid) {
          if (!$this->createmodules) {
            return;
          }
          $this->log_line('Creating module ' . $modulecode  . ', in school ID ' . $schoolid);
          $moduleid = \module_utils::add_modules($modulecode, $fullname, $active, $schoolid, $vle_api, $sms_api, $selfenroll,
              $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $this->db, $sms_import, $timed_exams,
              $exam_q_feedback, $add_team_members, $map_level, $academic_year_start);
          if ($moduleid) {
            $this->log_line('Created new modulecode: ' . $modulecode);
          } else {
            $this->log_line('Failed to created new modulecode: ' . $modulecode);
          }
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
        $updated = \module_utils::update_module_by_code($modulecode, $update, $this->db);
        if ($updated) {
          $this->log_line('Updated module: ' . $modulecode);
        } else {
          $this->log_line('Failed to update module: ' . $modulecode);
        }
        return $updated;
    }
  }

  /**
   * Get a SimleXML element from a DOMNode
   * @param DOMNode $domnode
   * @return SimpleXMLElement
   */
  protected function get_xml_element($domnode) {
    if (empty($domnode)) {
      return false;
    }
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
  protected function get_username_from_xml($xml) {
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
   * Process 'person' tags
   */
  protected function process_persons() {
    $this->log_line("Processing persons tag");
    $xml = $this->get_xml_reader();
    while ($xml->read()) {
      if ($this->validatexml and !$xml->isValid()) {
        throw new \Exception('Invalid XML');
      }
      if ($xml->name === 'person' && $xml->nodeType === \XMLReader::ELEMENT) {
        $node = $this->get_xml_element($xml->expand());
        if (!is_object($node)) {
          continue;
        }
        $this->process_person($node, $xml->readOuterXML());
      }
    }
    $xml->close();
  }

  /**
   * Process the person tag. This defines a Rogō user.
   *
   * @param string $node The raw contents of the XML element
   * @param stdClass $xml
   * @return void
   */
  protected function process_person($node, $xml) {
    // Get plugin configs.
    $person = new \stdClass();
    $person->idnumber = $this->get_person_studentid($xml);
    $firstname = (string) $node->name->n->given;
    $surname = (string) $node->name->n->family;
    // fn is mandatory in the dtd so use this if n not provided.
    if (empty($firstname)) {
      $fullname = (string) $node->name->fn;
      $fullnameparts = explode(' ', $fullname);
      $firstname = $fullnameparts[0];
    }
    if (empty($surname)) {
      $fullname = (string) $node->name->fn;
      $fullnameparts = explode(' ', $fullname);
      $surname = $fullnameparts[1];
    }
    $person->firstname = $firstname;
    $person->surname = $surname;
    $person->initials = $this->get_person_initials($xml);
    $person->title = (string) $node->name->n->prefix;
    $gender = (int) $node->demographics->gender;
    $person->gender = $this->get_person_gender($gender);
    $person->username = $this->get_username_from_xml($xml);
    // If username not found fall back to use the sourcedid.
    if ($person->username === '' and $this->sourcedidfailback) {
      $person->username = (string) $node->sourcedid->id;
    }

    $person->email = (string) $node->email;
    $person->full = (string) $node->description->full;
    $person->school = (string) $node->org->orgunit;
    $person->faculty = (string) $node->org->orgname;
    $person->role = strtolower((string) $node->institutionrole["institutionroletype"]);
    $validrole = ims_enterprise_roles::validate_role($person->role);
    if (empty($validrole)) {
      $this->log_line('User not created. Invalid role (' . $person->role . ') for user ' . $person->username . ' with email ' . $person->email);
      return;
    }

    $person->role = ucfirst($person->role);
    $person->startdate = substr((string) $node->timeframe->begin, -5, 5);
    $person->grade = (string) $node->extension->course; //TODO We should not use extension nodes unless PeopleSoft can make them available
    $person->yearofstudy = (string) $node->extension->year; //TODO We should not use extension nodes unless PeopleSoft can make them available

    // Fix case of some of the fields if required.
    if ($this->fixcaseusernames && isset($person->username)) {
      $person->username = strtolower($person->username);
    }
    if ($this->fixcasenames) {
      if (isset($person->firstname)) {
        $person->firstname = ucwords(strtolower($person->firstname));
      }
      if (isset($person->surname)) {
        $person->surname = ucwords(strtolower($person->surname));
      }
    }

    $recstatus = $this->detect_recstatus($node);

    // Now if the recstatus is 3, we should delete the user if-and-only-if the setting for delete users is turned on.
    if ($recstatus == self::RECORD_DELETE) {
      if ($this->deleteusers) { // If we're allowed to delete user records.
        $this->delete_user($person);
      } else {
        $this->log_line("Ignoring deletion request for user '$person->username' (ID number $person->idnumber).");
      }
    } else { // Add or update record.
      // If the user exists (matching sourcedid) then we don't need to do anything.
      $userid = \UserUtils::username_exists($person->username, $this->db);
      if (!$userid && $this->createusers) {
        // If they don't exist and haven't a defined username, we log this as a potential problem.
        if ((!isset($person->username)) || (strlen($person->username) == 0)) {
          $this->log_line("Cannot create new user for ID # $person->idnumber - no username listed in IMS data for this person.");
        } else {
          // If they don't exist and they have a defined username, and $createusers == true, we create them.
          $userid = \UserUtils::create_user($person->username, '', $person->title, $person->firstname, $person->surname, $person->email,
              $person->grade, $person->gender, $person->yearofstudy, $person->role, $person->idnumber, $this->db, $person->initials);
          $this->log_line("Created user record (' . $userid . ') for user '$person->username' (ID number $person->idnumber).");
        }
      } elseif ($userid && $this->createusers) {
        $this->log_line("Updating user '$person->username' (ID number $person->idnumber).");
        \UserUtils::update_user($userid, $person->username, '', $person->title, $person->firstname,
             $person->surname, $person->email, $person->grade, $person->gender, $person->yearofstudy, $person->role,
             $person->idnumber, $this->db, $person->initials);
        // It is totally wrong to mess with deleted users flag directly in database!!!
        // There is no official way to undelete user, sorry..
      } else {
        $this->log_line("No user record found for '$person->username' (ID number $person->idnumber).");
      }
    }
  }

  /**
   * Check whether the recstatus is valid
   * @param SimpleXMLElement $node xml node
   * @return string status code
   */
  protected function detect_recstatus($node) {
    if (is_null($node['recstatus'])) {
      return self::RECORD_CREATE;
    }
    $recstatus = (string) $node['recstatus'];
    if (!in_array($recstatus, array(self::RECORD_CREATE, self::RECORD_UPDATE, self::RECORD_DELETE))) {
      $recstatus = self::RECORD_UNDEFINED;
    }
    return $recstatus;
  }

  /**
   * Delete a user
   * @param stdClass $person
   */
  protected function delete_user($person) {
    $userid = \UserUtils::username_exists($person->username, $this->db);
    if ($userid !== false) {
        if (\UserUtils::user_paper_started($userid, $this->db)) {
          $this->log_line("Can not delete user '$person->username' (ID number $person->idnumber) - This user has already started at least one paper.");
          return;
        }
      try {
        \UserUtils::delete_userID($userid, $this->db);
        $this->log_line("Deleted user '$person->username' (ID number $person->idnumber).");
      } catch (\Exception $ex) {
        $this->log_line("Error deleting '$person->username' (ID number $person->idnumber).");
      }
    } else {
      $this->log_line("Can not delete user '$person->username' (ID number $person->idnumber) - user does not exist.");
    }
  }

  /**
   * Process the membership tag. This defines whether the specified Rogō users
   * should be added/removed as teachers/students.
   *
   * @param string $domnode The raw contents of the XML element
   */
  protected function process_memberships() {
    $this->log_line('Processing memberships');
    $xml = $this->get_xml_reader();
    while ($xml->read()) {
      if ($this->validatexml and !$xml->isValid()) {
        throw new \Exception('Invalid XML');
      }
      if ($xml->name === 'membership' && $xml->nodeType === \XMLReader::ELEMENT) {
        $node = $this->get_xml_element($xml->expand());
        if (!is_object($node)) {
          continue;
        }
        
        switch ($this->mapmoduleid) {
          case 'long':
            $modulecode = (string) $node->description->long;
            break;
          case 'short':
            $modulecode = (string) $node->description->short;
            break;
          case 'full':
            $modulecode = (string) $node->description->full;
            break;
          case 'coursecode':
            $modulecode = (string) $node->extension->course->code;
            break;
          case 'sourcedid':
          default:
            $modulecode = (string) $node->sourcedid->id;
            break;
        }
        
        foreach ($node->member as $member) {
          $this->process_member($member, $modulecode);
        }
      }
    }
    $xml->close();
  }

  /**
   * Process an individual member
   * @param stdClass $node
   */
  protected function process_member($member, $modulecode) {
    $studentid = (string) $member->role->userid;
    $username = $this->get_username($studentid);
    // If username not found fall back to use the sourcedid.
    if ($username === '' and $this->sourcedidfailback) {
      $username = (string) $member->sourcedid->id;
    }

    if ($this->capitafix) {
      $roletype = (string) $member->roletype;
    } else {
      $roletype = (string) $member->role['roletype'];
    }
    $role = $this->get_ims_setting('rolemap' . $roletype);
    $status = (string) $member->role->status;
    $this->log_line('Processing member: ' . $username);
    $userid = \UserUtils::studentid_exists($studentid, $this->db);
    $session = substr((string) $member->role->timeframe->begin, -10, 4);
    $attempt = (string) $member->extension->attempt;
    $action = 'added to';
    if ($status == self::ROLE_STATUS_ACTIVE && $role === 'Student') {
      $this->log_line("Adding user $userid to module $modulecode");
      $success = \UserUtils::add_student_to_module_by_name($userid, $modulecode, $attempt, $session, $this->db, 1);
    } else if ($status == self::ROLE_STATUS_ACTIVE) {
      $success = \UserUtils::add_staff_to_module_by_modulecode($userid, $modulecode, $this->db);
    } else if ($status == self::ROLE_STATUS_INACTIVE && $role === 'Staff') {
      $success = \UserUtils::remove_staff_from_module($userid, $modulecode, $this->db);
      $action = 'removed from';
    } else if ($status == self::ROLE_STATUS_INACTIVE && $role === 'Student') {
      $session = substr((string) $member->role->timeframe->begin, 0, 4);
      $success = \UserUtils::remove_student_from_module_by_modulecode($userid, $modulecode, $session, $this->db);
      $action = 'removed from';
    }

    if ($success) {
      $this->log_line('User (' . $username . ') ' . $action . ' Module ' . $modulecode . ' as a ' . $role);
    } else {
      $this->log_line('User (' . $username . ') could not be ' . $action . ' Module ' . $modulecode . ' as a ' . $role);
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
   * Get a particular IMS setting (as stored in the database)
   * @param string $property
   * @return string
   */
  protected function get_ims_setting($property) {
    if (property_exists($this, 'ims_settings') && is_object($this->ims_settings) && property_exists($this->ims_settings, $property)) {
      $setting = $this->ims_settings->{$property};
      if ($setting) {
        return $setting;
      }
    }
    return false;
  }

  /**
   * Store information about the last cron run
   * @param int $prev_time
   * @param string $prev_path
   * @param string $prev_md5
   */
  public function set_prev_configs($prev_time, $prev_path, $prev_md5) {
    $configObject = \Config::get_instance();
    $configObject->set_setting('prevtime', $prev_time, null, 'plugin_ims');
    $configObject->set_setting('prevpath', $prev_path, null, 'plugin_ims');
    $configObject->set_setting('prevmd5', $prev_md5, null, 'plugin_ims');
  }
}
