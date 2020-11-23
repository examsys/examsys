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
 * Utility class for role related functions.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @package core
 */
class Role
{
    /** @var int The database id of the role. */
    public $id;

    /** @var string The name of the role. */
    public $name;

    /** @var string The grouping the role is in. */
    public $grouping;

    /** @var int The group in the grouping that the role is in. */
    public $group;

    /** @var Role[] Cached list of all roles in the system, keyed by name. */
    protected static $roles;

    /** @var string The localised name of the role. */
    protected $localname;

    /**
     * Role constructor.
     *
     * @param int $id Database id of the role.
     * @param string $name The role.
     * @param string $grouping The grouping of the role.
     * @param int $group The group of the role.
     */
    public function __construct(int $id, string $name, string $grouping, int $group)
    {
        $this->id = $id;
        $this->name = $name;
        $this->grouping = $grouping;
        $this->group = $group;
    }

    /**
     * Gets all the roles for a user.
     *
     * @param int $user The database id of a user.
     * @return array
     */
    public static function getUsersRoles(int $user): array
    {
        $sql = 'SELECT r.id, r.name FROM user_roles ur JOIN roles r ON r.id = ur.roleid WHERE ur.userid = ?';
        $query = Config::get_instance()->db->prepare($sql);
        $query->bind_param('i', $user);
        $query->execute();
        $query->bind_result($id, $role);

        $roles = [];
        while ($query->fetch()) {
            $roles[$id] = $role;
        }
        $query->close();
        return $roles;
    }

    /**
     * Gets a list of roles in Rogo.
     *
     * @return Roles[]
     */
    public static function list(): array
    {
        if (is_null(static::$roles)) {
            // Cache the roles.
            $sql = 'SELECT id, `name`, `grouping`, `group` FROM roles';
            $query = Config::get_instance()->db->prepare($sql);
            $query->execute();
            $query->bind_result($id, $role, $grouping, $group);

            static::$roles = [];
            while ($query->fetch()) {
                static::$roles[$role] = new Role($id, $role, $grouping, $group);
            }
            $query->close();
        }
        return static::$roles;
    }

    /**
     * Puts each role into a bucket by grouping.
     *
     * @return Role[][] Multi-dimensional array first level is keyed by the grouping,
     *               which contains an array of Roles that are part of the grouping.
     */
    public static function listByGrouping(): array
    {
        $roles = static::list();
        $rolelist = [];
        foreach ($roles as $role) {
            $rolelist[$role->grouping][] = $role;
        }
        return $rolelist;
    }

    /**
     * Gets the localised name of a role name.
     *
     * @return string
     */
    public function localName(): string
    {
        if (empty($this->localname)) {
            // Lowercase the name and remove spaces.
            $strname = str_replace(' ', '', mb_strtolower($this->name));
            $langpack = new \langpack();
            $this->localname = $langpack->get_string('classes/role', $strname);
        }
        return $this->localname;
    }

    /**
     * Ensures that the user has the roles listed.
     *
     * @param int $user The database id of a user.
     * @param array $userroles The roles the user should be assigned.
     * @throws \InvalidRole
     */
    public static function updateRoles(int $user, array $userroles): void
    {
        if (count($userroles) < 1) {
            throw new InvalidRole('Users must have a role');
        }

        // Staff should be removed from module teams when they leave.
        $clear_staff_modules = false;

        // Admins should be removed from Schools when the loose the role.
        $clear_admin_access = false;

        // Get the users current roles.
        $existing = static::getUsersRoles($user);

        // Add the roles.
        $newcount = 0;
        $values = [];
        $newparams = [];
        foreach ($userroles as $role) {
            if (array_search($role, $existing) !== false) {
                // User already has the role.
                continue;
            }
            $newcount++;
            $values[] = '(?, ?) ';
            array_push($newparams, $user, static::validateRole($role));
            if ($role === 'left') {
                $clear_staff_modules = true;
            }
        }

        if ($newcount > 0) {
            // There are roles to add.
            $arguments = [];
            foreach ($newparams as &$param) {
                $arguments[] = &$param;
            }
            $newsql = 'INSERT INTO user_roles (userid, roleid) VALUES ' . implode(', ', $values);
            $newquery = Config::get_instance()->db->prepare($newsql);
            if ($newquery === false) {
                throw new coding_exception(Config::get_instance()->db->error);
            }
            $newtypes = [str_pad('', $newcount * 2, 'i')];
            call_user_func_array(array($newquery, 'bind_param'), array_merge($newtypes, $arguments));
            $newquery->execute();
            $newquery->close();
        }

        // Remove any roles not in the list.
        $removecount = 0;
        $removeparams = [$user];
        $placeholders = [];
        foreach ($existing as $roleid => $role) {
            if (array_search($role, $userroles) !== false) {
                // User still has the role.
                continue;
            }
            $removecount++;
            $removeparams[] = $roleid;
            $placeholders[] = '?';

            if ($role === 'Admin') {
                $clear_admin_access = true;
            }
        }

        if ($removecount > 0) {
            $arguments = [];
            foreach ($removeparams as &$param) {
                $arguments[] = &$param;
            }
            $placeholdersql = implode(', ', $placeholders);
            $removesql = "DELETE FROM user_roles WHERE userid = ? AND roleid IN($placeholdersql)";
            $removequery = Config::get_instance()->db->prepare($removesql);
            if ($removequery === false) {
                throw new coding_exception(Config::get_instance()->db->error);
            }
            $removetypes = [str_pad('', 1 + $removecount, 'i')];
            call_user_func_array(array($removequery, 'bind_param'), array_merge($removetypes, $arguments));
            $removequery->execute();
            $removequery->close();
        }

        // Clean up other data related to the user role changes.
        if ($clear_staff_modules) {
            UserUtils::clear_staff_modules_by_userID($user, Config::get_instance()->db);
        }

        if ($clear_admin_access) {
            UserUtils::clear_admin_access($user, Config::get_instance()->db);
        }
    }

    /**
     * Validates that the role name is correct.
     *
     * @param $name The name of the role to validate.
     * @return int The id of the role.
     * @throws \InvalidRole If the role does not exist
     */
    public static function validateRole($name): int
    {
        $roles = static::list();
        if (isset($roles[$name])) {
            return $roles[$name]->id;
        }
        // There is a coding error.
        throw new InvalidRole("'$name' is an invalid role");
    }

    /**
     * Validates that the combination of roles is allowed for a user.
     *
     * - There must be at least one role.
     * - Roles from multiple groupings are allowed.
     * - All roles from a grouping must be in the same group.
     *
     * @param array $roles Array of role names.
     * @throws \InvalidRole If the role combination is not valid.
     */
    public static function validateCombination(array $roles): void
    {
        if (empty($roles)) {
            throw new InvalidRole('Users must have a role');
        }
        $systemroles = static::list();
        $combo = [];
        foreach ($roles as $role) {
            if (!isset($systemroles[$role])) {
                // Role does not exist.
                throw new InvalidRole("'$role' is an invalid role");
            }
            $typeuser = isset($combo[$systemroles[$role]->grouping]);
            if ($typeuser and $combo[$systemroles[$role]->grouping] != $systemroles[$role]->group) {
                // All roles in the same grouping must be in the same group.
                throw new InvalidRole('Incompatible role role combination');
            }
            // Store the group we have found.
            $combo[$systemroles[$role]->grouping] = $systemroles[$role]->group;
        }
    }
}
