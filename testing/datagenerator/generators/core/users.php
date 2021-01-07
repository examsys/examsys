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

namespace testing\datagenerator;

use UserUtils;

/**
 * Generates Rogo users.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class users extends generator
{
    /** @var string[] An array of surnames that can be used for users. */
    protected static $surnames = array(
        'Ahmed',
        'Attack',
        'Baxter',
        'Beggan',
        'Bodart',
        'Brown',
        'Chéreau',
        'Cuī',
        'Davies',
        'de Barra',
        'De la Cour',
        'Édouard',
        'Fitch',
        'Fourt',
        'Gérin-Lajoie',
        'Groß',
        'Hanford',
        'Horáček',
        'Horáčková',
        'Horton',
        'Jiāng',
        'Kiông',
        'Köhler',
        'MacAleese',
        'Magill',
        'Miranowicz',
        'Müller',
        'Novák',
        'Nováková',
        'Oosthuizen',
        "O'Brien",
        'Ó Máille',
        'Owen',
        'Roberts',
        'Rockcliffe',
        'Schneider',
        'Tshûi',
        'Weiß',
        'Wilkinson',
        'Whitehead',
        'Wright',
        'Xue',
        'Zhōng',
    );
    /** @var string[] An array of forenames that can be used for users. */
    protected static $forenames = array(
        'Aleš',
        'Alvaro',
        'Andy',
        'Angelique',
        'Anne',
        'Anthony',
        'Alžběta',
        'Barry',
        'Bedřiška',
        'Božena',
        'Cecílie',
        'Clyde',
        'Corina',
        'Désirée',
        'Dušan',
        'Evžen',
        'Františka',
        'Freya',
        'Gabriela',
        'Gill',
        'Götz',
        'Hanuš',
        'Helen',
        'Ignác',
        'Izák',
        'Jeroným',
        'Joe',
        'Joeseph',
        'John',
        'Jonáš',
        'Klára',
        'Kristýna',
        'Laura',
        'Lütold',
        'Neill',
        'Nikodem',
        'Nigel',
        'Magdalena',
        'Oldřich',
        'Ondřejka',
        'Patricie',
        'René',
        'Shakeel',
        'Simon',
        'Suzanne',
        'Tadeáš',
        'Traudl',
        'Ulrich',
        'Vítězslav',
        'Wolfgang',
        'Yijun',
        'Zbyšek'
    );
    /** @var string[] An array of titles that can be used for users. */
    protected static $titles = array('Dr', 'Miss', 'Mr', 'Mrs', 'Mx', 'Prof');
    /** @var string[] All the valid roles for a user. */
    protected static $roles = array('Student', 'Staff', 'SysAdmin', 'Admin', 'graduate', 'left', 'External Examiner', 'Invigilator', 'Inactive Staff', 'Internal Reviewer', 'Standards Setter', 'Locked', 'Suspended');
    /** @var string[] Possible genders. */
    protected static $gender = array('Female', 'Male', 'Other');
    /** @var string[] possible years of study. */
    protected static $yearofstudy = array('0', '1', '2', '3', '4', '5');
    /** @var string[] Possible values for the grade field. */
    protected static $grades = array(
        'University Admin',
        'University Lecturer',
        'Technical Staff',
        'Staff External Examiner',
        'Staff Internal Reviewer',
        'Invigilator',
        'Standards Setter',
        'none',
        '',
    );
    /** @var string[] Possible initials for users. */
    protected static $initials = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
        'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    /** @var int Stores how many userd have been created. */
    protected static $userscreated = 0;
    /** @var string The base of the username to be used if one is not defined. */
    protected static $defaultusername = 'person';

    /**
     * Creates an item of metadata for a user.
     *
     * Parameters:
     * - type string The type of meta data.
     * - value string The value of the metadata.
     * - calendar_year int The id of the the year.
     *
     * @param int $userid Internal id of a Rogo user.
     * @param int $moduleid Internal id of a module.
     * @param array|stdClass $parameters
     * @return array Contains the values that were inserted into the database for the metadata.
     * @throws data_error
     */
    public function create_metadata(int $userid, int $moduleid, $parameters): array
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        // Check the user exists.
        if (!\UserUtils::userid_exists($userid, $this->db)) {
            throw new data_error("User {$userID} does not exist");
        }
        // Check the module exists.
        if (!\module_utils::get_moduleid_from_id($moduleid, $this->db)) {
            throw new data_error("Module {$moduleid} does not exist");
        }
        $yearutils = new \yearutils($this->db);
        $defaults = array(
            'userID' => $userid,
            'idMod' => $moduleid,
            'type' => 'SomeType',
            'value' => 'Default',
            'calendar_year' => $yearutils->get_current_session(),
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $this->insert_metadata($values);
        return $values;
    }

    /**
     * Create a Rogo user based on the parameters passed. The parameters should
     * correspond to fields in the users database table.
     *
     * Note there are two password paramters you can set:
     * - password: This is the encrypted password
     * - password_clear: plain text password
     * If neither is set then the username will be used as the password.
     *
     * @param array|stdClass $parameters
     * @throws data_error If passed parameter is invalid
     * @return array Contains the values that were inserted into the database for the user.
     */
    public function create_user($parameters)
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $usernumber = ++self::$userscreated;
        $defaults = array(
            'username' => self::$defaultusername . $usernumber,
            'surname' => $this->random_value('surnames'),
            'first_names' => $this->random_value('forenames'),
            'title' => $this->random_value('titles'),
            'email' => self::$defaultusername . $usernumber . '@example.com',
            'roles' => 'Student',
            'gender' => $this->random_value('gender'),
            'special_needs' => '0',
            'yearofstudy' => $this->random_value('yearofstudy'),
            'user_deleted' => null,
            'password_expire' => null,
            'grade' => $this->random_value('grades'),
            'initials' => $this->random_value('initials'),
            'password' => null,
            'sid' => null
        );

        // If a username has been sent in the parameters base the default email on it.
        if (!empty($parameters['email'])) {
            $defaults['email'] = $parameters['email'] . '@example.com';
        }

        // Ensure there is an encrypted password.
        if (empty($parameters['username'])) {
            $username = $defaults['username'];
        } else {
            $username = $parameters['username'];
        }
        if (empty($parameters['password_clear'])) {
            $plainpassword = $username;
        } else {
            $plainpassword = $parameters['password_clear'];
        }
        $defaults['password'] = \encryp::encpw(UserUtils::get_salt(), $username, $plainpassword);

        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $values['roles'] = $this->validate_roles($values['roles']);

        // Students require an id.
        if (in_array('Student', $values['roles']) and empty($values['sid'])) {
            throw new data_error('Must pass student id if user is a student');
        }

        // If not a student ensure student id is null.
        if (!in_array('Student', $values['roles'])) {
            $values['sid'] = null;
        }

        $values['id'] = $this->insert_user($values);

        // Add special needs.
        if (is_array($values['special_needs'])) {
            $special = $this->insertSpecial($values['id'], $values['special_needs']);
            $values['special_needs'] = '1';
            foreach ($special as $name => $value) {
                $values[$name] = $value;
            }
        }
        return $values;
    }

    /**
     * Add a paper note
     * @param array $parameters the paper note parameters
     *   int userID the user
     *   string note the note
     *   int paperID the paper the note refers to
     *   int authorID the author of the note
     *   int noteID the id of the existing note
     * @throws data_error
     */
    public function addPaperNote(array $parameters): void
    {
        if (
            empty($parameters['userID'])
            or empty($parameters['paperID'])
            or empty($parameters['authorID'])
        ) {
            throw new data_error('Error in | userID | paperID | authorID |');
        }

        $default = array(
            'note' => '',
            'noteID' => 0
        );
        $settings = $this->set_defaults_and_clean($default, $parameters);
        if ($settings['noteID'] === 0) {
            \StudentNotes::add_note(
                $parameters['userID'],
                $settings['note'],
                $parameters['paperID'],
                $parameters['authorID'],
                $this->db
            );
        } else {
            \StudentNotes::update_note($settings['note'], $settings['noteID'], $this->db);
        }
    }

    /**
     * Inserts metadata into the database.
     *
     * @param Array $data
     * @return void
     * @throws data_error
     */
    protected function insert_metadata(array $data): void
    {
        $sql = 'INSERT INTO users_metadata (userID, idMod, type, value, calendar_year) VALUES (?, ?, ?, ?, ?)';
        $query = $this->db->prepare($sql);
        $query->bind_param('iissi', $data['userID'], $data['idMod'], $data['type'], $data['value'], $data['calendar_year']);
        if (!$query->execute()) {
            // The metadata was not successfully inserted.
            $msg = "Metadata {$data['value']} for user {$data['userID']} on module {$data['userID']} was not inserted into database";
            throw new data_error($msg);
        }
    }

    /**
     * Inserts the user into the database.
     *
     * @param array $data
     * @throws data_error If passed parameter is invalid
     * @return int The id of the row inserted into the database.
     */
    protected function insert_user($data)
    {
        $sql = 'INSERT INTO users '
            . '(password, grade, surname, initials, username, title, email, first_names,'
            . ' gender, special_needs, yearofstudy, user_deleted, password_expire)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $query = $this->db->prepare($sql);
        $query->bind_param(
            'sssssssssiisi',
            $data['password'],
            $data['grade'],
            $data['surname'],
            $data['initials'],
            $data['username'],
            $data['title'],
            $data['email'],
            $data['first_names'],
            $data['gender'],
            $data['special_needs'],
            $data['yearofstudy'],
            $data['user_deleted'],
            $data['password_expire']
        );
        if (!$query->execute()) {
            // The user was not successfully inserted.
            throw new data_error("User {$data['username']} not inserted into database");
        }

        $userid = $query->insert_id;

        // Add student id.
        if (in_array('Student', $data['roles'])) {
            $result = $this->db->prepare('INSERT INTO sid VALUES(?, ?)');
            $result->bind_param('si', $data['sid'], $userid);
            $result->execute();
            $result->close();
        }

        \Role::updateRoles($userid, $data['roles']);

        return $userid;
    }

    /**
     * Ensures that all the roles are valid, if no valid roles are passed
     * use the first role in the classes roles array.
     *
     * @param string|array $roles
     * @return string
     */
    protected function validate_roles($roles)
    {
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
        // Ensure there is no white space before or after the role.
        foreach ($roles as $rolekey => $role) {
            $roles[$rolekey] = trim($role);
        }
        // Get only the valid roles.
        $validroles = [];
        foreach ($roles as $role) {
            try {
                \Role::validateRole($role);
                $validroles[] = $role;
            } catch (\InvalidRole $e) {
                // Ignore the role.
            }
        }
        if (empty($validroles)) {
            // Default to a student.
            $validroles = ['Student'];
        }
        return $validroles;
    }

    /**
     * Creates users spcial needs
     * @param integer $userID the user
     * @param array $special special needs data
     * @return array
     */
    protected function insertSpecial($userID, $special): array
    {
        $default_needs = array(
            'background' => null,
            'foreground' => null,
            'textsize' => 0,
            'extra_time' => null,
            'marks_color' => null,
            'themecolor' => null,
            'labelcolor' => null,
            'font' => null,
            'unansweredcolor' => null,
            'dismisscolor' => null,
            'medical' => null,
            'breaks' => null,
            'break_time' => null
        );
        $special = $this->set_defaults_and_clean($default_needs, $special);
        $result = $this->db->prepare('INSERT INTO special_needs VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $result->bind_param(
            'issiissssssssi',
            $userID,
            $special['background'],
            $special['foreground'],
            $special['textsize'],
            $special['extra_time'],
            $special['marks_color'],
            $special['themecolor'],
            $special['labelcolor'],
            $special['font'],
            $special['unansweredcolor'],
            $special['dismisscolor'],
            $special['medical'],
            $special['breaks'],
            $special['break_time']
        );
        $result->execute();
        $result->close();
        return $special;
    }
}
