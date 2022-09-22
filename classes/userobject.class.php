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
 * UserObject Class
 *
 * class for the currently logged in user and any functions related to this
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class UserObject extends RogoStaticSingleton
{
    protected static $inst = null;
    protected static $class_name = 'UserObject';
    protected static $dont_construct = true;

    private $password;
    private $userID;
    private $userroles;
    private $title;
    private $initials;
    private $first_names;
    private $surname;
    private $username;
    private $email;
    private $grade;
    private $year;
    private $special_needs;
    private $special_needs_percentage;

    /**
     * @var integer break percentage a user is allowed in an exam.
     */
    private $breaks = 0;
    private $record_no;
    private $split_username;
    private $demomode = false;
    private $roles;
    private $staffModules;
    private $staffTeamModules;
    private $studentModules;
    /** @var \mysqli The ExamSys database connection.*/
    private $db;
    private $configObj;

    // Special needs variables
    private $background;
    private $foreground;
    private $textsize;
    private $extra_time;
    private $marks_color;
    private $themecolor;
    private $labelcolor;
    private $font;
    private $unanswered;
    private $dismiss;
    private $globalthemecolour;
    private $globalthemefontcolour;
    private $highlightbgcolour;

    private $impersonateduser;

    /** @var string Language component name. */
    protected $langcomponent = 'classes/userobject';
    /** @var array language strings */
    protected $langstrings;

    /** @var string the default background colour for an exam */
    public const BGCOLOUR = '#FFFFFF';
    /** @var string the default foreground colour for an exam */
    public const FGCOLOUR = '#000000';
    /** @var string the default text size for an exam */
    public const TEXTSIZE = 100;
    /** @var string the default marks colour for an exam */
    public const MARKSCOLOUR = '#808080';
    /** @var string the default theme heading colour for an exam */
    public const THEMECOLOUR = '#316AC5';
    /** @var string the default label colour for an exam */
    public const LABELCOLOUR = '#C00000';
    /** @var string the default font family for an exam */
    public const FONT = 'Arial';
    /** @var string the default unaswered question colour for an exam */
    public const UNANSWEREDCOLOUR = '#FFC0C0';
    /** @var string the default dismiss option colour for an exam */
    public const DISMISSCOLOUR = '#A5A5A5';
    /** @var string the default global theme colour for an exam */
    public const GLOBALTHEMECOLOUR = '#5590CF';
    /** @var string the default global theme font colour for an exam */
    public const GLOBALTHEMEFONTCOLOUR = '#FFFFFF';
    /** @var string the default highlight option colour for an exam */
    public const HIGHLIGHTCOLOUR = '#FCF6CF';

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    /**
     * constructor
     *
     * @param mysqli $db is a mysqli link to db
     * @param Config $configObject a ExamSys config object populated from config.inc
     *
     * @return none
     */
    public function __construct($configObject, $db)
    {
        if (is_object(self::$inst)) {
            throw new Exception('Highlander:: there can be only one UserObject');
        }
        $this->db = & $db;
        $this->configObj = & $configObject;
        self::$inst = $this;

        $langpack = new \langpack();
        $this->langstrings = $langpack->get_all_strings($this->langcomponent);
    }
    /**
     * Destory UserObject
     *
     * Useful in unit tests.
     */
    public function destory()
    {
        self::$inst = null;
    }

    public function error_handling($context = null)
    {
        return error_handling($this);
    }

    public function get_bgcolor($default = '')
    {
        if (!isset($this->background) and $default != '') {
            $this->background = $default;
        }

        return $this->background;
    }

    public function get_fgcolor($default = '')
    {
        if (!isset($this->foreground) and $default != '') {
            $this->foreground = $default;
        }

        return $this->foreground;
    }

    public function get_textsize($default = '')
    {
        if ($this->textsize == 0 and $default != '') {
            $this->textsize = $default;
        }

        return $this->textsize;
    }

    public function get_marks_color($default = '')
    {
        if (!isset($this->marks_color) and $default != '') {
            $this->marks_color = $default;
        }

        return $this->marks_color;
    }

    public function get_themecolor($default = '')
    {
        if (!isset($this->themecolor) and $default != '') {
            $this->themecolor = $default;
        }

        return $this->themecolor;
    }

    public function get_labelcolor($default = '')
    {
        if (!isset($this->labelcolor) and $default != '') {
            $this->labelcolor = $default;
        }

        return $this->labelcolor;
    }

    public function get_font($default = '')
    {
        if (!isset($this->font) and $default != '') {
            $this->font = $default;
        }

        return $this->font;
    }

    public function get_unanswered_color($default = '')
    {
        if (!isset($this->unanswered) and $default != '') {
            $this->unanswered = $default;
        }

        return $this->unanswered;
    }

    public function get_dismiss_color($default = '')
    {
        if (!isset($this->dismiss) and $default != '') {
            $this->dismiss = $default;
        }

        return $this->dismiss;
    }

    /**
     * Get the users global theme colour preference
     * @param string $default default colour
     * @return string
     */
    public function getPaperGlobalThemeColour($default = '')
    {
        if (!isset($this->globalthemecolour) and $default != '') {
            $this->globalthemecolour = $default;
        }

        return $this->globalthemecolour;
    }

    /**
     * Get the users global theme font colour preference
     * @param string $default default colour
     * @return string
     */
    public function getPaperGlobalThemeFontColour($default = '')
    {
        if (!isset($this->globalthemefontcolour) and $default != '') {
            $this->globalthemefontcolour = $default;
        }

        return $this->globalthemefontcolour;
    }

    /**
     * Get the users highlight background colour preference
     * @param string $default default colour
     * @return string
     */
    public function getHighlightBackgroundColour($default = '')
    {
        if (!isset($this->highlightbgcolour) and $default != '') {
            $this->highlightbgcolour = $default;
        }

        return $this->highlightbgcolour;
    }

    /**
     * checks if user has role(s) specified
     *
     * @param $roles either a string or an array of strings
     * @param $exclusive if this should only have this role
     *
     * @return true if has role(s)
     */
    public function has_role($roles, $exclusive = 0)
    {
        if (is_string($roles)) {
            if ($exclusive == 0  or ($exclusive == 1 and count($this->roles) == 1)) {
                if (isset($this->roles[$roles])) {
                    return true;
                }
            }
        } else {
            // assume array
            if ($exclusive == 0 or ($exclusive == 1 and count($this->roles) == count($roles))) {
                foreach ($roles as $role) {
                    if (isset($this->roles[$role])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function is_temporary_account()
    {
        // Look for 'user' followed by one or more digits.
        return preg_match('/^user[0-9]+/', $this->username);
    }

    public function is_demo()
    {
        if ($this->demomode or $this->has_role('Demo')) {
            return true;
        }

        return false;
    }

    public function set_demo()
    {
        $this->demomode = true;
        $this->roles['Demo'] = 1;
    }

    /**
     * list the users roles
     *
     * @return array of the users roles
     */
    public function list_user_roles()
    {
        return array_keys($this->roles);
    }

    /**
     * returns the year of the user
     *
     * @return the year of the user
     */
    public function get_year()
    {
        return $this->year;
    }

    /**
     * returns the userID
     *
     * @return userID
     */
    public function &get_user_ID()
    {
        return $this->userID;
    }

    /**
     * @param string userID
     *
     * @return UserObject
     */
    public function set_user_ID($user_id)
    {
        $this->userID = $user_id;

        return $this;
    }

    /**
     * get the staff modules
     *
     * @return false if not staff else an array of the modules by id & CODE
     */
    public function get_staff_modules()
    {

        if (!$this->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
            //this is not a staff user so it cant be on any modules
            return false;
        }

        if (empty($this->staffModules)) {
            $this->load_staff_modules();
        }

        return $this->staffModules;
    }

    /**
     * get the staff members teams only (not a list of all modules thay can access
     * just their temas) used in /staff/index.php
     *
     * @return false if not staff else an array of the modules by id with idMod
     *         and fullName
     */
    public function get_staff_team_modules()
    {
        if (!$this->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
            //this is not a staff user so it cant be on any modules
            return false;
        }

        if (empty($this->staffTeamModules)) {
            $this->load_staff_team_modules();
        }

        return $this->staffTeamModules;
    }

    public function has_metadata($modIDs, $security_type, $security_value)
    {
        if (count($modIDs) == 0) {
            return false;
        }
        $has_data = true;

        $result = $this->db->prepare('SELECT users_metadata.userID FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN (' . implode(',', $modIDs) . ') AND userID = ? AND type = ? AND value = ?');
        $result->bind_param('iss', $this->get_user_ID(), $security_type, $security_value);
        $result->execute();
        $result->store_result();
        if ($result->num_rows == 0) {
            $has_data = false;
        }
        $result->close();

        return $has_data;
    }

    /**
     * @param string $moduleID an array of modules keyed on idMod
     *
     * @return bool true if staff member is on a module
     */
    public function is_staff_user_on_module($moduleID)
    {
        if (!$this->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
            //this is not a staff user so it cant be on any modules
            return false;
        }

        if (empty($this->staffModules)) {
            $this->load_staff_modules();
        }

        switch (gettype($moduleID)) {
            case 'array':
                if (count($moduleID) > 1) {
                    throw new Exception('is_staff_user_on_module:: only accepts one module at a time.');
                }
                foreach ($moduleID as $idMod => $full_moduleID) {
                    if (isset($this->staffModules[$idMod])) {
                        return true;
                    }
                }
                break;
            case 'string':
                if (in_array($moduleID, $this->staffModules)) {
                    return true;
                }
                break;
            case 'integer':
                if (isset($this->staffModules[$moduleID])) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * loads the staff modules
     *
     * @return the staff module list //TODO probably dont need the return
     */
    public function load_staff_modules()
    {
        $this->staffModules = array();

        if ($this->has_role('Admin')) {
            $result = $this->db->prepare('SELECT idMod, moduleID
        FROM modules_staff, modules
        WHERE modules_staff.idMod = modules.id and memberID = ?
        AND modules.moduleID IS NOT NULL
        and mod_deleted IS NULL
        UNION
        SELECT id, moduleID
        FROM modules, admin_access
        WHERE admin_access.schools_id = modules.schoolid
        AND userID = ?
        AND modules.moduleID IS NOT NULL
        and mod_deleted IS NULL');
            $result->bind_param('ii', $this->userID, $this->userID);
        } else {
            $result = $this->db->prepare('SELECT idMod, moduleID FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID = ? AND modules.moduleID IS NOT NULL AND mod_deleted IS NULL ORDER BY modules.moduleID');
            $result->bind_param('i', $this->userID);
        }
        $result->execute();
        $result->bind_result($idMod, $moduleID);
        while ($result->fetch()) {
            $this->staffModules[$idMod] = $moduleID;
        }
        $result->close();

        return $this->staffModules;
    }

    /**
     * loads the modules a staff member is explicitly on the team for
     * used in /staff/index.php
     *
     * @return array the staff module list
     */
    public function load_staff_team_modules()
    {
        $this->staffTeamModules = array();

        $standards_setter_sql = '';

        // Standards Setter should only see modules that allow standard setting
        if ($this->has_role('Standards Setter')) {
            $standards_setter_sql = "AND modules.checklist LIKE '%stdset%'";
        }

        $result = $this->db->prepare("SELECT idMod, moduleID, fullname FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID = ? AND active = 1 AND modules.moduleID IS NOT NULL AND mod_deleted IS NULL $standards_setter_sql ORDER BY modules.moduleID");

        $result->bind_param('i', $this->userID);
        $result->execute();

        $result->bind_result($idMod, $moduleID, $fullName);
        while ($result->fetch()) {
            $this->staffTeamModules[$idMod]['code'] = $moduleID;
            $this->staffTeamModules[$idMod]['fullName'] = $fullName;
        }
        $result->close();

        return $this->staffTeamModules;
    }

    /**
     * checks if user has special needs
     *
     * @return true if has special needs
     */
    public function is_special_needs()
    {
        if ($this->special_needs != 0) {
            return true;
        }

        return false;
    }

    /**
     * returns the grade of the user
     *
     * @return string grade
     */
    public function get_grade()
    {
        return $this->grade;
    }

    /**
     * Return the user's title
     *
     * @return string Title
     */
    public function get_title()
    {
        return $this->title;
    }

    public function get_temp_title()
    {
        return $this->temp_title;
    }

    /**
     * Return the user's initials
     *
     * @return string Initials
     */
    public function get_initials()
    {
        return $this->initials;
    }

    /**
     *  Return the user's first names
     *
     * @return string first_names
     */
    public function get_first_names()
    {
        return $this->first_names;
    }

    public function get_first_first_name()
    {
        $parts = explode(' ', $this->first_names);

        return $parts[0];
    }

    /**
     * Return the user's surname
     *
     * @return string Surname
     */
    public function get_surname()
    {
        return $this->surname;
    }

    public function get_temp_surname()
    {
        return $this->temp_surname;
    }

    /**
     * Return the user's username
     *
     * @return string username
     */
    public function &get_username()
    {
        return $this->username;
    }

    /**
     * Return the user's password
     *
     * @return string password
     */
    public function get_password()
    {
        return $this->password;
    }

    /**
     * Return the user's email address
     *
     * @return string email
     */
    public function get_email()
    {
        return $this->email;
    }

    /**
     * Return the user's special needs
     *
     * @return string password
     */
    public function get_special_needs()
    {
        return $this->special_needs;
    }

    /**
     * Return the user's special needs percentage
     *
     * @return string password
     */
    public function get_special_needs_percentage()
    {
        return $this->extra_time;
    }

    /**
     * Breaks percentage allowed in an exam
     *
     * @return integer
     */
    public function getRequiresBreaks()
    {
        return $this->breaks;
    }

    /**
     * Get a list of modules the current user has access to.
     *
     * @return array of staff module that this user has access to.
     */
    public function get_staff_accessable_modules($additional_mods = array())
    {
        $staff_modules_list = array();

        $staff_modules_sql = implode(',', array_keys($this->get_staff_modules()));
        $default_modules = array_keys($this->get_staff_modules());

        $new_array = array_merge($default_modules, $additional_mods);
        $staff_modules_sql = implode(',', array_unique($new_array));

        if ($staff_modules_sql != '' or $this->has_role(array('SysAdmin', 'Admin'))) {
            if ($this->has_role('SysAdmin')) {
                $sql = 'SELECT DISTINCT modules.id, moduleid, fullname, schools.code, school FROM modules, schools WHERE modules.schoolid = schools.id AND active = 1 AND mod_deleted IS NULL ORDER BY school, moduleID';
            } elseif ($this->has_role('Admin')) {
                $schoolIDs = implode(',', SchoolUtils::get_admin_schools($this->userID, $this->db));
                if ($schoolIDs != '') {
                    $sql = "(SELECT DISTINCT modules.id, moduleid, fullname, schools.code, school FROM modules, schools WHERE modules.schoolid = schools.id AND modules.id IN ($staff_modules_sql) AND active = 1 AND mod_deleted IS NULL) UNION (SELECT DISTINCT modules.id, moduleid, fullname, schools.code, school FROM modules, schools WHERE modules.schoolid = schools.id AND schoolid IN ($schoolIDs) AND active = 1 AND mod_deleted IS NULL) ORDER BY school, moduleID";
                } elseif ($staff_modules_sql != '') {
                    $sql = "SELECT DISTINCT modules.id, moduleid, fullname, schools.code, school FROM modules, schools WHERE modules.schoolid = schools.id AND modules.id IN ($staff_modules_sql) AND active = 1 AND mod_deleted IS NULL ORDER BY school, moduleID";
                } else {
                    // Admin is not on any Schools or Modules.
                    return $staff_modules_list;
                }
            } else {
                $sql = "SELECT DISTINCT modules.id, moduleid, fullname, schools.code, school FROM modules, schools WHERE modules.schoolid = schools.id AND modules.id IN ($staff_modules_sql) AND active = 1 AND mod_deleted IS NULL ORDER BY school, moduleID";
            }

            if (isset($sql)) {
                $result = $this->db->prepare($sql);
                $result->execute();
                $result->bind_result($idMod, $moduleid, $fullname, $schoolcode, $school);
                while ($result->fetch()) {
                    $staff_modules_list[$idMod]['schoolcode'] = $schoolcode;
                    $staff_modules_list[$idMod]['school'] = $school;
                    $staff_modules_list[$idMod]['id'] = $moduleid;
                    $staff_modules_list[$idMod]['idMod'] = $idMod;
                    $staff_modules_list[$idMod]['fullname'] = $fullname;
                }
                $result->close();
            }
        }

        return $staff_modules_list;
    }

    /**
     * loads the student modules
     *
     * @return array the student module list //TODO probably dont need the return
     */
    public function load_student_modules()
    {
        $this->studentModules = array();

        // studentmodule year -> module ->decode
        $result = $this->db->prepare('SELECT idMod, moduleID, calendar_year FROM modules_student, modules WHERE modules_student.idMod = modules.id AND userID = ? AND modules.moduleID IS NOT NULL AND mod_deleted IS NULL ORDER BY modules.moduleID'); //SELECT userID FROM modules_student WHERE userID=? AND idMod=? AND calendar_year=?");
        $result->bind_param('i', $this->get_user_ID());
        $result->execute();

        $result->bind_result($idMod, $moduleID, $calyear);
        while ($result->fetch()) {
            $this->studentModules[$calyear][$idMod] = $moduleID;
        }
        $result->close();

        return $this->studentModules;
    }

    /**
     * checks to see is user is on a student module
     *
     * @param $moduleID an integer or string of a module
     * @param $calendar_year the calendar year being looked for
     *
     * @return bool true if student member is on a module
     */
    public function is_student_user_on_module($moduleID, $calendar_year)
    {
        if (!$this->has_role('Student')) {
            //this is not a staff user so it cant be on any modules
            return false;
        }

        if (empty($this->studentModules)) {
            $this->load_student_modules();
        }

        switch (gettype($moduleID)) {
            case 'array':
                if (count($moduleID) > 1) {
                    throw new Exception('is_student_user_on_module:: only accepts one module at a time.');
                }
                foreach ($moduleID as $idMod => $full_moduleID) {
                    if (isset($this->studentModules[$calendar_year][$idMod])) {
                        return true;
                    }
                }
                break;
            case 'string':
                if (in_array($moduleID, $this->studentModules[$calendar_year])) {
                    return true;
                }
                break;
            case 'integer':
                if (isset($this->studentModules[$calendar_year][$moduleID])) {
                    return true;
                }
                break;
            default:
                return false;
        }

        return false;
    }

    /**
     * Enrole the student on a module.
     *
     * @param $idMod moduleID of module
     * @param $attempt
     * @param $session session of module
     * @param int $auto_update if system add
     *
     * @return bool return true if successful.
     */
    public function add_student_to_module($idMod, $attempt, $session, $auto_update = 0)
    {
        // need to check its a self reg module

        if (module_utils::get_full_details_by_ID($idMod, $this->db) === false) {
            return false;
        }
        if (UserUtils::is_user_on_module($this, $idMod, $session, $this->db)) {
            //don't add a user to a module multiple times
            return true;
        }
        $return = UserUtils::add_student_to_module($this->get_user_ID(), $idMod, $attempt, $session, $auto_update);

        $this->load_student_modules();

        return $return;
    }


    /**
     * add current user to module as staff
     *
     * @param $idMod
     */
    public function add_staff_to_module($idMod)
    {
        $return = UserUtils::add_staff_to_module($this->get_user_ID(), $idMod, $this->db);
        $this->load_staff_modules();

        return $return;
    }

    /**
     * remove current user to module as staff //not implimented
     *
     * @param $idMod
     */
    public function remove_staff_from_module($idMod)
    {
        // not implimented
        trigger_error('remove_staff_from_module not yet implimented', E_USER_WARNING);
    }

    public function store_original_user()
    {
        $data = new stdClass();

        $data->title            = $this->title;
        $data->initials         = $this->initials;
        $data->username         = $this->username;
        $data->surname          = $this->surname;
        $data->email            = $this->email;
        $data->roles            = $this->roles;

        $this->impersonatedfrom = $data;
    }

    public function impersonate($userid)
    {
        global $string;

        if ($this->has_role('SysAdmin')) {
            $this->store_original_user();
            $this->roles          = array();
            $this->staffModules   = array();
            $this->studentModules = array();
            $this->load($userid);
            $this->impersonate    = true;
        } else {
            $notice = UserNotices::get_instance();
            $notice->access_denied($this->db, $string, $string['impersonatepriv'], true, true);
        }
    }

    public function debug()
    {
        if ($this->impersonate === true) {
            echo $this->impersonatedfrom->title . ' ' . $this->impersonatedfrom->initials . ' ' . $this->impersonatedfrom->surname . ' (' . $this->impersonatedfrom->username . ') Impersonating: ';
        }
        echo $this->title . ' ' . $this->initials . ' ' . $this->surname . ' (' . $this->username . ') [' . implode(',', array_keys($this->roles)) . ']';
        echo "<br>\r\n";
    }

    public function is_impersonated()
    {
        return $this->impersonate;
    }

    public function load($userID)
    {
        $this->userID = $userID;
        $this->impersonate = false;

        $sql = "SELECT GROUP_CONCAT(r.name SEPARATOR ','), u.title, u.initials, u.surname, u.first_names, u.username, u.email, 
                       u.grade, u.yearofstudy, u.special_needs 
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.userid
                JOIN roles r ON r.id = ur.roleid
                WHERE u.user_deleted IS NULL AND u.id = ?
                GROUP BY u.id";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($this->userroles, $this->title, $this->initials, $this->surname, $this->first_names, $this->username, $this->email, $this->grade, $this->year, $this->special_needs);
        $stmt->fetch();
        $record_no = $stmt->num_rows();
        $stmt->close();
        if ($record_no == 0) {
            return false;
        }

        // Get special needs data. Any user can set their own settings for these.
        $stmt = $this->db->prepare('
            SELECT
                background,
                foreground,
                textsize,
                marks_color,
                themecolor,
                labelcolor,
                font,
                unanswered,
                dismiss,
                globalthemecolour,
                globalthemefont_colour,
                highlight_bgcolour
            FROM
                special_needs
            WHERE
                userID = ?
        ');
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result(
            $this->background,
            $this->foreground,
            $this->textsize,
            $this->marks_color,
            $this->themecolor,
            $this->labelcolor,
            $this->font,
            $this->unanswered,
            $this->dismiss,
            $this->globalthemecolour,
            $this->globalthemefontcolour,
            $this->highlightbgcolour
        );
        $stmt->fetch();
        $stmt->close();

        // Add additional special needs data. These can only be set by admins for a user.
        if ($this->special_needs == 1) {
            $stmt = $this->db->prepare('
                SELECT
                    extra_time,
                    break_time
                FROM
                    special_needs
                WHERE
                    userID = ?
            ');
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result(
                $this->extra_time,
                $this->breaks,
            );
            $stmt->fetch();
            $stmt->close();
        }

        // Add temporary account data.
        if ($this->is_temporary_account()) {
            $stmt = $this->db->prepare('SELECT title, first_names, surname FROM temp_users WHERE assigned_account = ?');
            $stmt->bind_param('s', $this->get_username());
            $stmt->execute();
            $stmt->bind_result($this->temp_title, $this->temp_first_names, $this->temp_surname);
            $stmt->fetch();
            $stmt->close();
        }

        $temp = explode(',', $this->userroles);

        foreach ($temp as $value) {
            $this->roles[$value] = 1;
        }
        unset($this->userroles);
    }

    public function db_user_change()
    {
        global $db_errors, $string;

        $configObject = Config::get_instance();

        $getback = array('cfg_db_sysadmin_user', 'cfg_db_sysadmin_passwd', 'cfg_db_admin_user', 'cfg_db_admin_passwd', 'cfg_db_staff_user', 'cfg_db_staff_passwd', 'cfg_db_student_user', 'cfg_db_student_passwd', 'cfg_db_external_user', 'cfg_db_external_passwd', 'cfg_db_internal_user', 'cfg_db_internal_passwd', 'cfg_db_inv_user', 'cfg_db_inv_passwd', 'cfg_db_database');

        $arr = $this->configObj->get($getback);
        foreach ($arr as $k => $v) {
            ${$k} = $v;
        }

        // Select the aproprate database user
        if ($this->has_role('SysAdmin')) {
            $result = $this->db->change_user($cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database);
        } elseif ($this->has_role(array('Staff', 'Admin'))) { // Process staff first to get higher priority than students
            $result = $this->db->change_user($cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database);
        } elseif ($this->has_role('Student')) {
            $result = $this->db->change_user($cfg_db_student_user, $cfg_db_student_passwd, $cfg_db_database);
        } elseif ($this->has_role('External Examiner')) {
            $result = $this->db->change_user($cfg_db_external_user, $cfg_db_external_passwd, $cfg_db_database);
        } elseif ($this->has_role('Internal Reviewer')) {
            $result = $this->db->change_user($cfg_db_internal_user, $cfg_db_internal_passwd, $cfg_db_database);
        } elseif ($this->has_role('Invigilator')) {
            $result = $this->db->change_user($cfg_db_inv_user, $cfg_db_inv_passwd, $cfg_db_database);
        } else {
            $result = false;

            // new security routine
            $notice = UserNotices::get_instance();
            if (!is_array($this->roles) or (isset($this->roles['']) and $this->roles[''] == 1)) {
                $notice->access_denied($this->db, $string, '', true, true);
            } else {
                $notice->access_denied($this->db, $string, sprintf($string['denied_role'], implode(',', array_keys($this->roles))), true, true);
            }
        }
        if ($result == false) {
            $msg = 'This should never appear, please contact support';
            $support_email = support::get_email();

            if ($support_email != '') {
                $msg .= " (<a href=\"$support_email\">$support_email</a>)";
            }
            $msg .= '.';
            $notice = UserNotices::get_instance();
            $notice->display_notice('Change DB user failed', $msg, '../artwork/exclamation_64.png', '#C00000', true, false);
            if ($this->db->error) {
                echo $this->langstrings['showerror'] . '<br >';
                echo "<body>\n</html>";
                exit();
            }
        }
    }

    /**
     * Check if the user has completed a paper
     * @param integer $id - paper id
     * @return bool true if user has completed the paper
     */
    public function user_completed_paper($id)
    {
        $result = $this->db->prepare('SELECT NULL FROM log_metadata WHERE userID = ? and paperID = ? and completed IS NOT null');
        $result->bind_param('ii', $this->userID, $id);
        $result->execute();
        $result->store_result();
        if ($result->num_rows > 0) {
            $result->close();
            return true;
        }
        $result->close();
        return false;
    }

    /**
     * Set accessibility settings.
     *
     * This is called by the user themselves. It does not update break, extra time and medical conditions.
     *
     * @param ?string $bgcolor the paper back ground colour
     * @param ?string $fgcolor the paper font colour
     * @param ?int $textsize the paper text size
     * @param ?string $marks_color the question marks font colour
     * @param ?string $themecolor the paper theme section font colour
     * @param ?string $labelcolor the question labels font colour
     * @param ?string $font the paper font family
     * @param ?string $unanswered_color the unanswered question colour
     * @param ?string $dismiss_color the dimissed answer colour
     * @param ?string $paper_global_themecolour the system theme colour
     * @param ?string $paper_global_themefont_colour the system theme font colour
     * @param ?string $highlight_bgcolour the question highlight colour
     */
    public function userSetAccessibility(
        ?string $bgcolor,
        ?string $fgcolor,
        ?int $textsize,
        ?string $marks_color,
        ?string $themecolor,
        ?string $labelcolor,
        ?string $font,
        ?string $unanswered_color,
        ?string $dismiss_color,
        ?string $paper_global_themecolour,
        ?string $paper_global_themefont_colour,
        ?string $highlight_bgcolour
    ): void {
        $result = $this->db->prepare('SELECT 1 FROM special_needs WHERE userID = ?');
        $result->bind_param('i', $this->userID);
        $result->execute();
        $result->store_result();

        if ($result->num_rows < 1) {
            // The user has no entry, so we need to create a special needs entry for them.
            $insert = $this->db->prepare('INSERT INTO special_needs (userID) VALUES (?)');
            $insert->bind_param('i', $this->userID);
            $insert->execute();
            $insert->close();
        }
        $result->close();

        // Default values if null provided.
        if (is_null($bgcolor)) {
            $bgcolor = self::BGCOLOUR;
        }
        if (is_null($fgcolor)) {
            $fgcolor = self::FGCOLOUR;
        }
        if (is_null($marks_color)) {
            $marks_color = self::MARKSCOLOUR;
        }
        if (is_null($textsize)) {
            $textsize = self::TEXTSIZE;
        }
        if (is_null($font)) {
            $font = self::FONT;
        }
        if (is_null($themecolor)) {
            $themecolor = self::THEMECOLOUR;
        }
        if (is_null($labelcolor)) {
            $labelcolor = self::LABELCOLOUR;
        }
        if (is_null($unanswered_color)) {
            $unanswered_color = self::UNANSWEREDCOLOUR;
        }
        if (is_null($dismiss_color)) {
            $dismiss_color = self::DISMISSCOLOUR;
        }
        if (is_null($paper_global_themecolour)) {
            $paper_global_themecolour = self::GLOBALTHEMECOLOUR;
        }
        if (is_null($paper_global_themefont_colour)) {
            $paper_global_themefont_colour = self::GLOBALTHEMEFONTCOLOUR;
        }
        if (is_null($highlight_bgcolour)) {
            $highlight_bgcolour = self::HIGHLIGHTCOLOUR;
        }

        // Log changes.
        $changes = 0;
        $logger = new Logger($this->db);
        if ($bgcolor != $this->get_bgcolor(self::BGCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_bgcolor(),
                $bgcolor == '' ? self::BGCOLOUR : $bgcolor,
                'background'
            );
            $changes++;
        }

        if ($fgcolor != $this->get_fgcolor(self::FGCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_fgcolor(),
                $fgcolor == '' ? self::FGCOLOUR : $fgcolor,
                'foreground'
            );
            $changes++;
        }

        if ($marks_color != $this->get_marks_color(self::MARKSCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_marks_color(),
                $marks_color == '' ? self::MARKSCOLOUR : $marks_color,
                'marks'
            );
            $changes++;
        }

        if ($textsize != $this->get_textsize(self::TEXTSIZE)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_textsize(self::TEXTSIZE),
                $textsize == 0 ? self::TEXTSIZE : $textsize,
                'textsize'
            );
            $changes++;
        }

        if ($font != $this->get_font(self::FONT)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_font(self::FONT),
                $font == '' ? self::FONT : $font,
                'font'
            );
            $changes++;
        }

        if ($themecolor != $this->get_themecolor(self::THEMECOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_themecolor(self::THEMECOLOUR),
                $themecolor == '' ? self::THEMECOLOUR : $themecolor,
                'theme'
            );
            $changes++;
        }

        if ($labelcolor != $this->get_labelcolor(self::LABELCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_labelcolor(self::LABELCOLOUR),
                $labelcolor == '' ? self::LABELCOLOUR : $labelcolor,
                'label'
            );
            $changes++;
        }

        if ($unanswered_color != $this->get_unanswered_color(self::UNANSWEREDCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_unanswered_color(self::UNANSWEREDCOLOUR),
                $unanswered_color == '' ? self::UNANSWEREDCOLOUR : $unanswered_color,
                'unanswered'
            );
            $changes++;
        }

        if ($dismiss_color != $this->get_dismiss_color(self::DISMISSCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->get_dismiss_color(self::DISMISSCOLOUR),
                $dismiss_color == '' ? self::DISMISSCOLOUR : $dismiss_color,
                'dismiss'
            );
            $changes++;
        }

        if ($paper_global_themecolour != $this->getPaperGlobalThemeColour(self::GLOBALTHEMECOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->getPaperGlobalThemeColour(self::GLOBALTHEMECOLOUR),
                $paper_global_themecolour == '' ? self::GLOBALTHEMECOLOUR : $paper_global_themecolour,
                'globaltheme'
            );
            $changes++;
        }

        if ($paper_global_themefont_colour != $this->getPaperGlobalThemeFontColour(self::GLOBALTHEMEFONTCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->getPaperGlobalThemeFontColour(self::GLOBALTHEMEFONTCOLOUR),
                $paper_global_themefont_colour == '' ? self::GLOBALTHEMEFONTCOLOUR : $paper_global_themefont_colour,
                'globalthemefont'
            );
            $changes++;
        }

        if ($highlight_bgcolour != $this->getHighlightBackgroundColour(self::HIGHLIGHTCOLOUR)) {
            $logger->track_change(
                'User Profile',
                $this->userID,
                $this->userID,
                $this->getHighlightBackgroundColour(self::HIGHLIGHTCOLOUR),
                $highlight_bgcolour == '' ? self::HIGHLIGHTCOLOUR : $highlight_bgcolour,
                'highlight'
            );
            $changes++;
        }
        // Insert new settings.
        if (
            $changes > 0
        ) {
            $result = $this->db->prepare(
                'UPDATE special_needs 
                 SET background = ?, foreground = ?, textsize = ?, marks_color = ?, themecolor = ?, 
                     labelcolor = ?, font = ?, unanswered = ?, dismiss = ?, globalthemecolour = ?, 
                     globalthemefont_colour =?, highlight_bgcolour = ?
                 WHERE userID = ?'
            );
            $result->bind_param(
                'ssisssssssssi',
                $bgcolor,
                $fgcolor,
                $textsize,
                $marks_color,
                $themecolor,
                $labelcolor,
                $font,
                $unanswered_color,
                $dismiss_color,
                $paper_global_themecolour,
                $paper_global_themefont_colour,
                $highlight_bgcolour,
                $this->userID
            );
            $result->execute();
            $result->close();
        }
    }
}
