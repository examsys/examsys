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
use \encryp,
    \UserUtils;

/**
 * Generates Rogo users.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class users extends generator {
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
  protected static $roles = array(0 => 'Student', 'Staff', 'SysAdmin', 'Admin', 'graduate', 'left', 'External Examiner', 'Invigilator', 'Inactive Staff');
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
    'Invigilator',
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
   * Create a Rogo user based on the parameters passed. The parameters should
   * correspond to fields in the users database table.
   *
   * Note there are two password paramters you can set:
   * - password: This is the encrypted password
   * - password_clear: plain text password
   * If neither is set then the username will be used as the password.
   *
   * @param array|stdClass $parameters
   * @return array Contains the values that were inserted into the database for the user.
   * @throws data_error
   */
  public function create_user($parameters) {
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
    );
    // If a username has been sent in the parameters base the detfault email on it.
    if (!empty($parameters['email'])) {
      $defaults['email'] = $parameters['email'] . '@example.com';
    }

    // Ensure there is an encrypted password.
    $encrypt = new encryp();
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
    $defaults['password'] = $encrypt->encpw(UserUtils::get_salt(), $username, $plainpassword);

    $values = $this->set_defaults_and_clean($defaults, $parameters);
    $values['roles'] = $this->validate_roles($values['roles']);

    $values['id'] = $this->insert_user($values);
    return $values;
  }

  /**
   * Inserts the user into the database.
   *
   * @param array $data
   * @return int The id of the row inserted into the database.
   * @throws data_error
   */
  protected function insert_user($data) {
    $db = loader::get_database();
    $sql = "INSERT INTO users "
        . "(password, grade, surname, initials, username, title, email, roles, first_names,"
        . " gender, special_needs, yearofstudy, user_deleted, password_expire)"
        . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $db->prepare($sql);
    $query->bind_param('ssssssssssiisi', $data['password'], $data['grade'], $data['surname'], $data['initials'], $data['username'],
        $data['title'], $data['email'], $data['roles'], $data['first_names'], $data['gender'], $data['special_needs'],
        $data['yearsofstudy'], $data['user_deleted'], $data['password_expire']);
    if (!$query->execute()) {
      // The user was not successfully inserted.
      throw new data_error("User {$data['username']} not inserted into database");
    }
    return $query->insert_id;
  }

  /**
   * Ensures that all the roles are valid, if no valid roles are passed
   * use the first role in the classes roles array.
   *
   * @param string|array $roles
   * @return string
   */
  protected function validate_roles($roles) {
    if (!is_array($roles)) {
      $roles = explode(',', $roles);
    }
    // Ensure there is no white space before or after the role.
    foreach ($roles as $rolekey => $role) {
      $roles[$rolekey] = trim($role);
    }
    // Get only the valid roles.
    $validroles = array_intersect($roles, self::$roles);
    $validroles = implode(',', $validroles);
    if (empty($validroles)) {
      // Take the first valid role if none of the passed roles are valid.
      $validroles = self::$roles[0];
    }
    return $validroles;
  }
}
