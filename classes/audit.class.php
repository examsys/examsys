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

use plugins\ims\ims_enterprise;

/**
 * Utility class for auditing
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */
class Audit
{

    /**
     * @var string Language pack component.
     */
    public const LANGCOMPONENT = 'classes/audit';

    /**
     * @var string add role event
     */
    public const ADDROLE = 'Add-Role';

    /**
     * @var string remove role event
     */
    public const REMOVEROLE = 'Remove-Role';

    /**
     * @var string add enrolment event
     */
    public const ADDENROLMENT = 'Add-Enrolment';

    /**
     * @var string remove enrolment event
     */
    public const REMOVEENROLMENT = 'Remove-Enrolment';

    /**
     * @var string add team member event
     */
    public const ADDTEAMMEMBER = 'Add-Team-Member';

    /**
     * @var string remove team member event
     */
    public const REMOVETEAMMEMBER = 'Remove-Team-Member';

    /**
     * Get the object type for a given action.
     * @param string $action the action
     * @return string
     */
    private static function getObjectType(string $action): string
    {
        $langpack = new langpack();
        switch ($action) {
            case self::ADDROLE:
            case self::REMOVEROLE:
                $type = 'role';
                break;
            default:
                $type = 'module';
                break;
        }
        return $langpack->get_string(self::LANGCOMPONENT, $type);
    }

    /**
     * Map object of event to a navigable page
     *
     * @param string $objects comma seperated list of objects
     * @param string $action the action type
     * @param int $userid the user the event relates to
     * @return array
     */
    private static function mapObject(string $objects, string $action, int $userid): array
    {
        $configObject = Config::get_instance();
        $webroot = $configObject->get('cfg_root_path');
        switch ($action) {
            case self::ADDROLE:
            case self::REMOVEROLE:
                $mapsto[] = array(
                    'url' => $webroot . '/users/details.php?userID=' . $userid,
                    'label' => $objects,
                );
                break;
            default:
                foreach (explode(',', $objects) as $obj) {
                    $mapsto[] = array(
                        'url' => $webroot . '/module/index.php?module='
                        . module_utils::get_idMod($obj, $configObject->db),
                        'label' => $obj,
                    );
                }
                break;
        }
        return $mapsto;
    }

    /**
     * Map source of event to a navigable page
     * @param string $source cause of the event
     * @param string $details details of the event
     * @param int $userid the user the event relates to
     * @return array
     */
    private static function mapSource(string $source, string $details, int $userid): array
    {
        $langpack = new langpack();
        $configObject = Config::get_instance();
        $webroot = $configObject->get('cfg_root_path');
        switch ($source) {
            case '/module/do_edit_team.php':
                // In this case details equates to the module code.
                $mapsto = array(
                    'url' => $webroot . '/module/index.php?module='
                    . module_utils::get_idMod($details, $configObject->db),
                    'label' => $langpack->get_string(self::LANGCOMPONENT, 'module'),
                );
                break;
            case '/self_enrol.php':
                // In this case details equates to the module code.
                $mapsto = array(
                    'url' => $webroot . '/module/index.php?module='
                    . module_utils::get_idMod($details, $configObject->db),
                    'label' => $langpack->get_string(self::LANGCOMPONENT, 'selfenrol'),
                );
                break;
            case '/LTI/index.php':
                $mapsto = array(
                    'url' => $webroot . '/LTI/lti_keys_list.php',
                    'label' => $langpack->get_string(self::LANGCOMPONENT, 'lti'),
                );
                break;
            case '/users/details.php':
            case '/users/create_new_user.php':
            case '/users/do_edit_modules.php':
            case '/module/do_edit_multi_team.php':
                $mapsto = array(
                    'url' => $webroot . '/users/details.php?userID=' . $userid,
                    'label' => $langpack->get_string(self::LANGCOMPONENT, 'userdetails'),
                );
                break;
            case '/api/modulemanagement/enrol':
                $mapsto = array(
                    'url' => $webroot . '/admin/oauth/list_oauth.php',
                    'label' => $langpack->get_string(self::LANGCOMPONENT, 'enrolapi'),
                );
                break;
            case '/api/usermanagement':
                $mapsto = array(
                    'url' => $webroot . '/admin/oauth/list_oauth.php',
                    'label' => $langpack->get_string(self::LANGCOMPONENT, 'userapi'),
                );
                break;
            case '/admin/do_add_module.php':
                // This will be an SMS sync.
                $mapsto = array(
                    'url' => $webroot . '/admin/sms_import_summary.php',
                    'label' => $langpack->get_string(self::LANGCOMPONENT, 'sms'),
                );
                break;
            default:
                $url = '';
                if (preg_match('#plugins\/ims\/*#', $source)) {
                    // IMS cron import.
                    $ims = new ims_enterprise($configObject->db);
                    return $ims->getSourceForAudit();
                } elseif (preg_match('#plugins\/SMS#', $source)) {
                    // SMS syncs.
                    $url = $webroot . '/admin/sms_import_summary.php';
                    $source = $langpack->get_string(self::LANGCOMPONENT, 'sms');
                } elseif (preg_match('#cli\/init.php#', $source)) {
                    // System install.
                    $url = $webroot . '/users/details.php?userID=' . $userid;
                    $source = $langpack->get_string(self::LANGCOMPONENT, 'systeminstall');
                }
                $mapsto = array(
                    'url' => $url,
                    'label' => $source,
                );
                break;
        }

        return $mapsto;
    }

    /**
     * Get all events
     * @param string $starttime the start time to get events from
     * @param int $limit results per page
     * @param int $page current page
     * @return array
     */
    public static function getEvents(string $starttime, int $limit, int $page): array
    {
        $configObject = Config::get_instance();
        $webroot = $configObject->get('cfg_root_path');
        $langpack = new langpack();

        $search = new AuditSearch();
        $search->setLimit($limit);
        $search->setPage($page);
        $search->setTime($starttime);
        // execute query
        $result = $search->execute();

        $first = $result->first;
        $last = $result->last;
        $pages = $result->pages;
        $stmt = $result->query;

        $stmt->bind_result($userID, $action, $time, $sourceID, $source, $details);
        $stmt->store_result();
        $events = array();
        while ($stmt->fetch()) {
            if ($sourceID > 0) {
                $sourceuser = UserUtils::get_username($sourceID, $configObject->db);
            } else {
                $sourceuser = $langpack->get_string(self::LANGCOMPONENT, 'system');
            }
            $objects = json_decode($details);
            if (is_array($objects)) {
                $objects = implode(',', $objects);
            } else {
                $objects = $details;
            }

            $events['from'] = number_format($first);
            $events['to'] = number_format($last);
            $events['total'] = number_format($result->total);
            $events['pages'] = $pages;
            $langpack = new langpack();
            $events['items'][] = array(
                'object' => self::mapObject($objects, $action, $userID),
                'objecttype' => self::getObjectType($action),
                'time' => $time,
                'user' => $sourceuser,
                'affecteduser' => array(
                    'url' => $webroot . '/users/details.php?userID=' . $userID,
                    'label' => UserUtils::get_username($userID, Config::get_instance()->db),
                ),
                'source' => self::mapSource($source, $objects, $userID),
                'eventtype' => $langpack->get_string(self::LANGCOMPONENT, $action),
            );
        }
        $stmt->close();
        return $events;
    }

    /**
     * Insert a permission event
     *
     * @param string $action the action
     * @param int $userid the user affected
     * @param string $details the action details
     * @throws coding_exception
     */
    public static function insertEvent(string $action, int $userid, string $details): void
    {
        if (PHP_SAPI != 'cli') {
            $url = Url::fromGlobals();
            $path = $url->getRogoPath();
            $query = $url->getQueryAsArray();
        } else {
            $path = $_SERVER['PHP_SELF'];
            $query = '';
        }
        $now = date('Y-m-d H:i:s');
        $userObj = UserObject::get_instance();
        $configObject = Config::get_instance();
        if (!is_null($userObj)) {
            $sourceid = $userObj->get_user_ID();
        } else {
            if (isset($query['access_token'])) {
                $oauth = new \oauth($configObject);
                $sourceid = $oauth->getClientFromAccess($query['access_token']);
            } else {
                $sourceid = -1;
            }
        }
        $sql = 'INSERT INTO audit_log (userID, action, details, time, sourceID, source)
            VALUES (?, ?, ?, ?, ?, ?)';
        $query = $configObject->db->prepare($sql);
        $query->bind_param('isssis', $userid, $action, $details, $now, $sourceid, $path);
        if ($query === false) {
            throw new coding_exception($configObject->db->error);
        }
        $query->execute();
        $query->close();
    }

    /**
     * Get the retention period for the data.
     * @return ?int
     */
    public static function getRententionPeriod(): ?int
    {
        $sql = 'SELECT days FROM retention WHERE `table` = ?';
        $query = Config::get_instance()->db->prepare($sql);
        $table = 'audit_log';
        $query->bind_param('s', $table);
        $query->execute();
        $query->store_result();
        $query->bind_result($days);
        $query->fetch();
        $query->close();
        return $days;
    }

    /**
     * Set the retention period
     * @param int $days retention period
     */
    public static function setRetentionPeriod(int $days): void
    {
        $current = self::getRententionPeriod();
        if ($current !== $days) {
            $sql = 'UPDATE retention SET days = ? WHERE `table` = ?';
            $query = Config::get_instance()->db->prepare($sql);
            $table = 'audit_log';
            $query->bind_param('is', $days, $table);
            $query->execute();
            $query->close();
        }
    }

    /**
     * Delete audit permissions data according to the data retention policy.
     * @return boolean
     */
    public static function deleteDataByRetentionPolicy(): bool
    {
        $retention = Audit::getRententionPeriod();
        if (empty($retention)) {
            return false;
        }
        $retentionperiod = date('Y-m-d H:i:s', strtotime('-' . $retention . ' day'));
        $sql = 'DELETE FROM audit_log WHERE time < ?';
        $query = Config::get_instance()->db->prepare($sql);
        $query->bind_param('s', $retentionperiod);
        $query->execute();
        $query->close();
        return true;
    }
}
