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
 * Class that searches for users.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class UserSearch extends Search
{
    /** @var bool Flags if administrators should be returned. */
    protected $admin = false;

    /** @var bool Flags if external reviewers should be returned. */
    protected $external = false;

    /** @var bool Flags if graduates should be returned. */
    protected $graduates = false;

    /** @var string A student id number to search for. */
    protected $idnumber;

    /** @var bool Flags if inactive staff should be returned. */
    protected $inactive = false;

    /** @var string The initials for the user. */
    protected $initials;

    /** @var bool Flags if internal reviewers should be returned. */
    protected $internal = false;

    /** @var bool Flags if invigilators should be returned. */
    protected $invigilators = false;

    /** @var bool Flags if leavers should be returned. */
    protected $leavers = false;

    /** @var bool Flags if locked students should be returned. */
    protected $locked = false;

    /** @var int The internal id of a module that the users should be linked to. */
    protected $module;

    /** @var string[] Parts of a name for the user. */
    protected $names = [];

    /** @var string The ordering clause for the query. */
    protected $orderby = 'surname ASC';

    /** @var bool Stores if any role has been selected for the user search. */
    protected $role_selected = false;

    /** @var bool Flags if staff should be returned. */
    protected $staff = false;

    /** @var bool Flags if standrard setters should be returned. */
    protected $standard = false;

    /** @var bool Flags if students should be returned. */
    protected $students = false;

    /** @var bool Flags if suspended students should be returned. */
    protected $suspended = false;

    /** @var bool Flags if system administrators should be returned. */
    protected $sysadmin = false;

    /** @var string The title for the user. */
    protected $title;

    /** @var string A username to search for. */
    protected $username;

    /** @var int The year the students should be associated with. */
    protected $year;

    /**
     * @see \Search::doQuery()
     *
     * @return \mysqli_stmt
     */
    public function doQuery(): mysqli_stmt
    {
        // Generate the fields we wish to return.
        $fields = implode(', ', $this->getFields());

        // Generate the conditional SQL.
        list($studentmods, $staffmods) = $this->generateModuleSQL();
        list($year, $staffyear) = $this->generateYearSQL();
        $name = $this->generateNameSQL();
        $username = $this->generateUsernameSQL();
        $idnumber = $this->generateIDNumberSQL();
        $roles = $this->generateRolesSQL();
        $not_deleted = $this->generateNotDeletedSQL();

        // Query for finding students.
        $student_tables = $this->generateStudentTableSQL();
        $student_where = SQLFragment::combine(' AND ', $not_deleted, $year, $studentmods, $name, $username, $idnumber, $roles);
        $student_query = "SELECT $fields FROM $student_tables WHERE $student_where->sql GROUP BY users.id, sid.student_id";

        if (isset($this->module)) {
            // Query for finding staff.
            $staff_tables = $this->generateStaffTableSQL();
            // Need to get only staff,student roles to avoid duplication.
            $staff_where = SQLFragment::combine(' AND ', $not_deleted, $staffyear, $staffmods, $name, $username, $idnumber, $roles);
            $staff_query = "SELECT $fields FROM $staff_tables WHERE $staff_where->sql GROUP BY users.id, sid.student_id";

            $sql = "($student_query) UNION ($staff_query) ORDER BY $this->orderby LIMIT $this->limit OFFSET $this->offset";
        } else {
            // When there is no module filter we do not need the separate staff search part of the query.
            $staff_where = new SQLFragment();
            $sql = "$student_query ORDER BY $this->orderby LIMIT $this->limit OFFSET $this->offset";
        }

        $query = Config::get_instance()->db->prepare($sql);

        if ($query === false) {
            throw new \RuntimeException(Config::get_instance()->db->error);
        }

        // Prepare the query.
        $types = [$student_where->param_types . $staff_where->param_types];

        $arguments = [];
        foreach ($student_where->params as &$param) {
            $arguments[] = &$param;
        }
        foreach ($staff_where->params as &$param) {
            $arguments[] = &$param;
        }

        if (call_user_func_array(array($query, 'bind_param'), array_merge($types, $arguments)) === false) {
            throw new \RuntimeException($query->error);
        }

        if ($query->execute() === false) {
            throw new \RuntimeException($query->error);
        }

        return $query;
    }

    /**
     * Returns the SQL to filter by student idnumber.
     *
     * @return \SQLFragment
     */
    protected function generateIDNumberSQL(): SQLFragment
    {
        $idnumber = new SQLFragment();
        if (isset($this->idnumber)) {
            $idnumber->sql = 'student_id = ?';
            $idnumber->addParameter($this->idnumber, SQLFragment::TYPE_STRING);
        }
        return $idnumber;
    }

    /**
     * Returns the SQL to filter by module association.
     *
     * @return array|\SQLFragment[]
     */
    protected function generateModuleSQL(): array
    {
        $studentmods = new SQLFragment();
        $staffmods = new SQLFragment();

        if (isset($this->module)) {
            $studentmods->sql = 'modules_student.idMod = ?';
            $studentmods->addParameter($this->module, SQLFragment::TYPE_INTEGER);

            $staffmods->sql = 'modules_staff.idMod = ?';
            $staffmods->addParameter($this->module, SQLFragment::TYPE_INTEGER);
        }

        return [$studentmods, $staffmods];
    }

    /**
     * Returns the SQL to filter students by a year.
     *
     * @return \SQLFragment[]
     */
    protected function generateYearSQL(): array
    {
        $studentyear = new SQLFragment();
        $staffyear = new SQLFragment();
        if (isset($this->year)) {
            $studentyear->sql = 'calendar_year = ?';
            $studentyear->addParameter($this->year, SQLFragment::TYPE_INTEGER);
            $staffyear->sql = '1 <> 1';
        }
        return [$studentyear, $staffyear];
    }

    /**
     * Returns the SQL to filter by name.
     *
     * @return \SQLFragment
     */
    protected function generateNameSQL(): SQLFragment
    {
        $sql = new SQLFragment();
        $conditions = [];

        // Add the title.
        if (isset($this->title)) {
            $conditions[] = 'title = ?';
            $sql->addParameter($this->title, SQLFragment::TYPE_STRING);
        }

        // Add the initials.
        if (isset($this->initials)) {
            $conditions[] = 'initials LIKE ?';
            $sql->addParameter($this->initials . '%', SQLFragment::TYPE_STRING);
        }

        // Add the name parts.
        $names = [];
        foreach ($this->names as $name) {
            $names[] = 'surname LIKE ? OR first_names LIKE ?';
            $sql->addParameter($name, SQLFragment::TYPE_STRING);
            $sql->addParameter($name, SQLFragment::TYPE_STRING);
        }

        // Combine the name parts into the contitions.
        if (!empty($names)) {
            $conditions[] = '(' . implode(' OR ', $names) . ')';
        }

        // Build the fragment.
        $sql->sql = implode(' AND ', $conditions);

        return $sql;
    }

    /**
     * Returns the SQL to filter out deleted users.
     *
     * @return string
     */
    protected function generateNotDeletedSQL(): SQLFragment
    {
        $sql = new SQLFragment();
        $sql->sql = 'user_deleted IS NULL';
        return $sql;
    }

    /**
     * Returns the SQL to filter users by role.
     *
     * @return \SQLFragment
     */
    protected function generateRolesSQL(): SQLFragment
    {
        $rolesfragment = new SQLFragment();
        $leftfragment = new SQLFragment();
        $roles = [];
        $fragment = 'roles.name = ?';

        if ($this->students or isset($this->idnumber)) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Student', SQLFragment::TYPE_STRING);
        }

        if ($this->staff) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Staff', SQLFragment::TYPE_STRING);
        }

        if ($this->admin) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Admin', SQLFragment::TYPE_STRING);
        }

        if ($this->sysadmin) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('SysAdmin', SQLFragment::TYPE_STRING);
        }

        if ($this->standard) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Standards Setter', SQLFragment::TYPE_STRING);
        }

        if ($this->inactive) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Inactive Staff', SQLFragment::TYPE_STRING);
        }

        if ($this->external) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('External Examiner', SQLFragment::TYPE_STRING);
        }

        if ($this->internal) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Internal Reviewer', SQLFragment::TYPE_STRING);
        }

        if ($this->invigilators) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Invigilator', SQLFragment::TYPE_STRING);
        }

        if ($this->graduates) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('graduate', SQLFragment::TYPE_STRING);
        }

        if ($this->leavers) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('left', SQLFragment::TYPE_STRING);
        } else {
            $leftfragment->sql = 'grade <> ?';
            $leftfragment->addParameter('left', SQLFragment::TYPE_STRING);
        }

        if ($this->suspended) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Suspended', SQLFragment::TYPE_STRING);
        }

        if ($this->locked) {
            $roles[] = $fragment;
            $rolesfragment->addParameter('Locked', SQLFragment::TYPE_STRING);
        }

        if (count($roles) > 0) {
            $rolesfragment->sql = '(' . implode(' OR ', $roles) . ')';
        }

        return SQLFragment::combine(' AND ', $rolesfragment, $leftfragment);
    }

    /**
     * Returns the SQL to filter by username.
     *
     * @return \SQLFragment
     */
    protected function generateUsernameSQL(): SQLFragment
    {
        $username = new SQLFragment();
        if (isset($this->username)) {
            $username->sql = 'users.username LIKE ?';
            $username->addParameter($this->username, SQLFragment::TYPE_STRING);
        }
        return $username;
    }

    /**
     * Returns the tables that should be used to search for staff in the query.
     *
     * @return string
     */
    protected function generateStaffTableSQL(): string
    {
        $tables = 'users
          JOIN user_roles ON users.id = user_roles.userid
          JOIN roles ON user_roles.roleid = roles.id
          JOIN modules_staff ON users.id = modules_staff.memberID 
          LEFT JOIN sid ON users.id = sid.userID
          LEFT JOIN modules ON modules_staff.idMod = modules.id';
        return $tables;
    }

    /**
     * Returns the tables that should be used to search for students in the query.
     *
     * @return string
     */
    protected function generateStudentTableSQL(): string
    {
        $tables = 'users
          JOIN user_roles ON users.id = user_roles.userid
          JOIN roles ON user_roles.roleid = roles.id
          LEFT JOIN modules_student ON users.id = modules_student.userID
          LEFT JOIN sid ON users.id = sid.userID
          LEFT JOIN modules ON modules_student.idMod = modules.id';
        return $tables;
    }

    /**
     * Gets the fields that will be used in the query.
     *
     * @return array|string[]
     */
    protected function getFields(): array
    {
        return [
                'users.id',
                "GROUP_CONCAT(DISTINCT roles.name  SEPARATOR ',')",
                'student_id',
                'surname',
                'initials',
                'first_names',
                'title',
                'users.username',
                'grade',
                'yearofstudy',
                'email',
                'special_needs',
        ];
    }

    /**
     * Gets valid titles for users.
     *
     * @return array
     */
    protected function getTitles(): array
    {
        $string = LangUtils::loadlangfile('include/titles.php', []);
        $titles = explode(',', $string['title_types']);
        return $titles;
    }

    /**
     * Parses any initinals the user entered.
     *
     * @param string $name The remaining name search string.
     * @return string The search string with initials removed.
     */
    protected function parseInitials(string $name): string
    {
        $sections = preg_split('[,.]', $name);
        if (count($sections) > 1) {    // Search for initials.
            if (mb_strlen($sections[0]) < mb_strlen($sections[1])) {
                $this->initials = trim($sections[0]);
                $name = trim($sections[1]);
            } else {
                $this->initials = trim($sections[1]);
                $name = trim($sections[0]);
            }
        }
        return $name;
    }

    /**
     * Parse individual names from the search string.
     *
     * @param string $search
     */
    protected function parseName(string $search): void
    {
        $names = explode(' ', $search);
        foreach ($names as $name) {
            if (false === array_key_exists($name, $this->names)) {
                $this->names[$name] = $name;
            }
        }
    }

    /**
     * Parses out any titles in the name search string.
     *
     * @param string $name The remaining name search string.
     * @return string The search string with titles removed.
     */
    protected function parseTitle(string $name): string
    {
        $tmp_titles = $this->getTitles();
        foreach ($tmp_titles as $tmp_title) {
            $pattern = '/(^|\s)(' . $tmp_title . ')($|\s)/i';
            if (preg_match($pattern, $name)) {
                $this->title = $tmp_title;

                // Remove the title.
                $name = preg_replace($pattern, ' ', $name);

                // We found a title, so no need to search for more.
                break;
            }
        }
        return $name;
    }

    /**
     * Returns if a role was selected by the query.
     *
     * @return bool
     */
    public function rolesSelected(): bool
    {
        return $this->role_selected;
    }

    /**
     * Sets an id number that should be used to filter the search.
     *
     * @param string $idnumber
     */
    public function setIDNumber(string $idnumber): void
    {
        $idnumber = trim($idnumber);
        if ($idnumber === '') {
            // Do not save empty idnumbers.
            return;
        }
        $this->idnumber = $idnumber;
    }

    /**
     * Sets the module that users must be associated with.
     *
     * @param int $module
     */
    public function setModule(int $module): void
    {
        $this->module = $module;
    }

    /**
     * The name that should be searched for
     *
     * It may contain the initials, forename, surname and title valid searched could look like:
     *
     * Initials must include a comma or full stop, the shortest part will be used:
     * - Miss Cox, G
     * - Miss G, Cox
     * - G, Miss Cox
     * - Cox, GA
     * - G, Cox
     *
     * - Miss Cox
     * - Cox
     * - Georgina
     * - Georgina Cox
     * - Miss Georgina Cox
     *
     * An asterisk is assumed to mean match anything in the names:
     *
     * - Georg* Co*
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        // Replace any asterisks with the SQL wildcard.
        $tmp_surname = str_replace('*', '%', trim($name));

        // Lookup titles.
        $tmp_surname = $this->parseTitle($tmp_surname);

        // Find initials.
        $tmp_surname = $this->parseInitials($tmp_surname);

        // Find remaining names,
        $this->parseName($tmp_surname);
    }

    /**
     * Sets the ordering of the search results.
     *
     * @param string $field The name of a field that will be returned.
     * @param string $direction ASC or DESC
     */
    public function setOrder(string $field, string $direction): void
    {
        if (array_search($field, $this->getFields()) === false) {
            // Ordering is only allowed on a field we are returning.
            return;
        }

        if (strpos($field, '.') !== false) {
            // Remove the table name from the field, since the order is on a UNION.
            $parts = explode('.', $field);
            $field = $parts[1];
        }

        if (mb_strtoupper($direction) === 'DESC') {
            $this->orderby = "$field DESC";
        } else {
            $this->orderby = "$field ASC";
        }
    }

    /**
     * Set that the search should include administrators.
     */
    public function setSearchAdmin(): void
    {
        $this->admin = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include external examiners.
     */
    public function setSearchExternal(): void
    {
        $this->external = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include graduates.
     */
    public function setSearchGraduates(): void
    {
        $this->graduates = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include inactive staff.
     */
    public function setSearchInactive(): void
    {
        $this->inactive = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include internal examiners.
     */
    public function setSearchInternal(): void
    {
        $this->internal = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include invigilators.
     */
    public function setSearchInvigilators(): void
    {
        $this->invigilators = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include leavers.
     */
    public function setSearchLeavers(): void
    {
        $this->leavers = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include locked students.
     */
    public function setSearchLocked(): void
    {
        $this->locked = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include staff.
     */
    public function setSearchStaff(): void
    {
        $this->staff = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include standard setters
     */
    public function setSearchStandard(): void
    {
        $this->standard = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include students.
     */
    public function setSearchStudents(): void
    {
        $this->students = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include suspended students.
     */
    public function setSearchSuspended(): void
    {
        $this->suspended = true;
        $this->role_selected = true;
    }

    /**
     * Set that the search should include system administrators.
     */
    public function setSearchSysAdmin(): void
    {
        $this->sysadmin = true;
        $this->role_selected = true;
    }

    /**
     * Sets the username that should be searched for.
     *
     * Asterisks will be replaced with the SQL wildcard.
     *
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $username = trim($username);
        if ($username === '') {
            // Do not save empty usernames.
            return;
        }
        $this->username = str_replace('*', '%', $username);
    }

    /**
     * Sets the year that students must be enrolled in.
     *
     * @param mixed $year
     */
    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    /**
     * Counts the total number of results.
     *
     * @return int
     */
    protected function totalResults(): int
    {
        // Generate the conditional SQL.
        list($studentmods, $staffmods) = $this->generateModuleSQL();
        list($year, $staffyear) = $this->generateYearSQL();
        $name = $this->generateNameSQL();
        $username = $this->generateUsernameSQL();
        $idnumber = $this->generateIDNumberSQL();
        $roles = $this->generateRolesSQL();
        $not_deleted = $this->generateNotDeletedSQL();

        // Query for finding students.
        $student_tables = $this->generateStudentTableSQL();
        $student_where = SQLFragment::combine(' AND ', $not_deleted, $year, $studentmods, $name, $username, $idnumber, $roles);
        $student_query = "SELECT users.id FROM $student_tables WHERE $student_where->sql";

        if (isset($this->module)) {
            // Query for finding staff.
            $staff_tables = $this->generateStaffTableSQL();
            // Need to get only staff,student roles to avoid duplication.
            $staff_where = SQLFragment::combine(' AND ', $not_deleted, $staffyear, $staffmods, $name, $username, $idnumber, $roles);
            $where = str_replace('Student', 'Staff,Student', $staff_where->sql);
            $staff_query = "SELECT users.id FROM $staff_tables WHERE $where";

            $sql = "SELECT COUNT(id) FROM ($student_query UNION DISTINCT $staff_query) AS users";
        } else {
            // When there is no module filter we do not need the separate staff search part of the query.
            $staff_where = new SQLFragment();
            $sql = "SELECT COUNT(DISTINCT id) FROM ($student_query) AS users";
        }

        $query = Config::get_instance()->db->prepare($sql);

        if ($query === false) {
            throw new \RuntimeException(Config::get_instance()->db->error);
        }

        // Prepare the query.
        $types = [$student_where->param_types . $staff_where->param_types];

        $arguments = [];
        foreach ($student_where->params as &$param) {
            $arguments[] = &$param;
        }
        foreach ($staff_where->params as &$param) {
            $arguments[] = &$param;
        }

        if (call_user_func_array(array($query, 'bind_param'), array_merge($types, $arguments)) === false) {
            throw new \RuntimeException($query->error);
        }

        if ($query->execute() === false) {
            throw new \RuntimeException($query->error);
        }

        $counter = 0;
        // fetch total items count
        $query->bind_result($count);
        while ($query->fetch()) {
            $counter += $count;
        }
        $query->close();

        return $counter;
    }
}
