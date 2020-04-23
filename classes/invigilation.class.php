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
 *  Invigilator accessor methods
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class Invigilation
{
    /**
     * @var mysqli The database object
     */
    private $db;

    /**
     * @var Config The config object
     */
    private $config;

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
     * Constructor.
     */
    public function __construct()
    {
        $configObject = Config::get_instance();
        $this->db = $configObject->db;
        $this->config = $configObject;
    }

    /**
     * Check if user has completed the exam.
     * @param int $userid
     * @param int $paperid
     * @return boolean
     */
    private function getCompleted(int $userid, int $paperid): bool
    {
        $completed = true;
        $sql = 'SELECT completed FROM log_metadata WHERE userID = ? AND paperID = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $userid, $paperid);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($completed);
        $stmt->fetch();
        if (is_null($completed)) {
            $completed = false;
        }
        $stmt->close();
        return $completed;
    }

    /**
     * Get emergency numbers
     * @return array
     */
    public function emergencyNumbers(): array
    {
        $data = array();
        $contact1 = $this->config->get_setting('core', 'emergency_support_contact1');
        $contact2 = $this->config->get_setting('core', 'emergency_support_contact2');
        $contact3 = $this->config->get_setting('core', 'emergency_support_contact3');
        $contacts = array($contact1, $contact2, $contact3);
        foreach ($contacts as $contact) {
            if (!empty($contact['name']) and !empty($contact['number'])) {
                $data[$contact['name']] = $contact['number'];
            }
        }
        return $data;
    }

    /**
     * Get the student list for a paper
     * @param string $modules
     * @param PaperProperties $property_object
     * @param LogLabEndTime|boolean $log_lab_end_time lab end time object / false if not in a lab
     * @param boolean $allow_timing
     * @return array
     */
    public function getStudents(
        string $modules,
        PaperProperties $property_object,
        $log_lab_end_time,
        bool $allow_timing
    ): array {
        $paperID = $property_object->get_property_id();

        $configObject = Config::get_instance();

        // Create a caching LogExtraTime gets all the results in one hit.
        if ($log_lab_end_time !== false) {
            $log_extra_time = new LogExtraTime($log_lab_end_time, array(), $this->db, true);
        }

        // Get any student notes.
        $notes_array = PaperNotes::get_all_notes_by_paper($paperID, $this->db);

        // Get any student toilet breaks.
        $toilet_break_array = ToiletBreaks::get_all_breaks_by_paper($paperID, $this->db);

        // Get guest account details.
        $guest_accounts = array();
        $sql = 'SELECT assigned_account, title, first_names, surname FROM temp_users';
        $results = $this->db->prepare($sql);
        $results->execute();
        $results->bind_result($assigned_account, $title, $first_names, $surname);
        while ($results->fetch()) {
            $guest_accounts[$assigned_account] = array(
                'title' => $title,
                'first_names' => $first_names,
                'surname' => $surname
            );
        }
        $results->close();

        // Get all students who should are able to access this paper
        $sql = 'SELECT
        DISTINCT extra_time, medical, breaks, modules_student.userID, surname, first_names, title
        FROM modules_student, users
        LEFT JOIN special_needs ON users.id = special_needs.userID
        WHERE idMod IN ( ' . $modules . ')
        AND calendar_year = ?
        AND modules_student.userID = users.id';
        $results = $this->db->prepare($sql);
        $session = $property_object->get_calendar_year();
        $results->bind_param('s', $session);
        $results->execute();
        $results->store_result();
        $results->bind_result($extra_time_percentage, $medical, $breaks, $userID, $surname, $first_names, $title);
        $student_object = array();

        while ($results->fetch()) {
            $student_object[$userID]['user_ID'] = $userID;
            $student_object[$userID]['surname'] = $surname;
            $student_object[$userID]['first_names'] = $first_names;
            $student_object[$userID]['title'] = $title;
            $student_object[$userID]['extra_time_percentage'] = $extra_time_percentage;
            $student_object[$userID]['medical'] = $medical;
            $student_object[$userID]['breaks'] = $breaks;
        }
        $results->close();

        // Merge in all students who have submitted records for this paper
        $sql = 'SELECT
            DISTINCT sn.extra_time, sn.medical, sn.breaks, lm.userID, u.surname, u.first_names, u.title
            FROM log_metadata lm
            INNER JOIN users u ON lm.userID = u.id
            LEFT JOIN special_needs sn ON u.id = sn.userID
            WHERE lm.paperID = ?
            AND u.username LIKE "user%"';
        $results = $this->db->prepare($sql);
        $results->bind_param('i', $paperID);
        $results->execute();
        $results->store_result();
        $results->bind_result($extra_time_percentage, $medical, $breaks, $userID, $surname, $first_names, $title);
        while ($results->fetch()) {
            if ($first_names == 'Temporary Account') {
                $username = strtolower($surname);

                $student_object[$userID]['user_ID'] = $userID;
                $student_object[$userID]['surname'] = $guest_accounts[$username]['surname'];
                $student_object[$userID]['first_names'] = $guest_accounts[$username]['first_names'];
                $student_object[$userID]['title'] = $guest_accounts[$username]['title'];
                $student_object[$userID]['extra_time_percentage'] = 0;
                $student_object[$userID]['medical'] = '';
                $student_object[$userID]['breaks'] = '';
            } else {
                $student_object[$userID]['user_ID'] = $userID;
                $student_object[$userID]['surname'] = $surname;
                $student_object[$userID]['first_names'] = $first_names;
                $student_object[$userID]['title'] = $title;
                $student_object[$userID]['extra_time_percentage'] = $extra_time_percentage;
                $student_object[$userID]['medical'] = $medical;
                $student_object[$userID]['breaks'] = $breaks;
            }
        }
        $results->close();

        $column = 'surname';
        $sort_order = 'asc';
        $student_object = \sort::array_csort($student_object, $column, $sort_order);

        $data = array();
        foreach ($student_object as $student_id => $student_obj) {
            try {
                if ($log_lab_end_time !== false) {
                    $data[] = $this->processLabStudentList(
                        $log_lab_end_time,
                        $log_extra_time,
                        $student_obj,
                        $property_object,
                        $notes_array,
                        $toilet_break_array,
                        $allow_timing
                    );
                } else {
                    $data[] = $this->processStudentList(
                        $student_obj,
                        $property_object,
                        $notes_array,
                        $toilet_break_array,
                        $allow_timing
                    );
                }
            } catch (ErrorException $e) {
                // Skip students with bad data.
                $logger = new \logger($this->db);
                $userObj = \UserObject::get_instance();
                $userid = $userObj->get_user_ID();
                $type = 'Invigilation';
                $errorline = __LINE__ - 15;
                $logger->record_application_warning($userid, $type, $e->getMessage(), $e->getFile(), $errorline);
            }
        }
        return $data;
    }

    /**
     * Generate the in lab student list
     * @param LogLabEndTime $log_lab_end_time
     * @param LogExtraTime $log_extra_time
     * @param array $student_object
     * @param PaperProperties $property_object
     * @param array $notes_array
     * @param array $toilet_break_array
     * @param bool $allow_timing
     * @throws ErrorException
     * @return array
     */
    public function processLabStudentList(
        LogLabEndTime $log_lab_end_time,
        LogExtraTime $log_extra_time,
        array $student_object,
        PaperProperties $property_object,
        array $notes_array,
        array $toilet_break_array,
        bool $allow_timing
    ): array {
        $data = array();
        // Determine when the current exam session will end

        $lab_session_end_datetime = $log_lab_end_time->get_session_end_date_datetime();

        if ($lab_session_end_datetime == false) {
            $lab_session_end_datetime = $log_lab_end_time->calculate_default_session_end_datetime();
        }

        $exam_duration_mins = $property_object->get_exam_duration();

        $class = 'student';

        if ($exam_duration_mins == null) {
            throw new ErrorException('Exam duration is mandatory in summative exams');
        }

        if (is_int($exam_duration_mins) === false) {
            throw new ErrorException('$exam_duration_mins ' . $exam_duration_mins . ' must be an integer');
        }

        $exam_duration_interval = new DateInterval('PT' . $exam_duration_mins . 'M');
        $lab_session_start_datetime = clone $lab_session_end_datetime;
        $lab_session_start_datetime->sub($exam_duration_interval);

        // Determine when the student's exam session will end

        // Set userID log_extra_time as we are in cached mode
        $log_extra_time->set_student_object($student_object);

        $student_end_datetime = $lab_session_end_datetime;

        // Highlight student's who have gone over time

        $current_datetime = new DateTime();

        // Calculate extra time

        $extra_time_secs = $log_extra_time->get_extra_time_secs();
        $extra_time_mins = round($extra_time_secs / 60);

        $special_needs_extra_time_mins = ($exam_duration_mins / 100) * $student_object['extra_time_percentage'];
        $special_needs_extra_time_secs = (int)($special_needs_extra_time_mins * 60);
        $total_extra_time = $extra_time_secs + $special_needs_extra_time_secs;

        $total_extra_time_interval = new DateInterval('PT' . $total_extra_time . 'S');

        $student_end_datetime = $student_end_datetime->add($total_extra_time_interval);

        $ft = clone $student_end_datetime;
        $ft->setTimezone(new DateTimeZone($property_object->get_timezone()));
        $formatted_end_time = $ft->format($this->config->get('cfg_short_time_php'));

        if ($extra_time_secs > 0 or $special_needs_extra_time_secs > 0) {
            $formatted_end_time = '<strong>' . $formatted_end_time . '</strong>';
        }

        // Get student description
        $tmp_userID = $student_object['user_ID'];
        $surname = $student_object['surname'];
        $first_names = $student_object['first_names'];
        $title = $student_object['title'];
        $paperID = $property_object->get_property_id();

        $has_student_exceeded_end = ($student_end_datetime < $current_datetime);

        if ($has_student_exceeded_end) {
            $class .= ' redwarn';
        }

        $data['class'] = $class;
        $data['id'] = $paperID . '_' . $tmp_userID;
        $data['paperid'] = $paperID;
        $data['userid'] = $tmp_userID;
        $data['timing'] = $allow_timing ? 'true' : 'false';
        $data['completed'] = $this->getCompleted($student_object['user_ID'], $paperID);
        $data['notes'] = 0;
        if (isset($notes_array[$tmp_userID]) and $notes_array[$tmp_userID] == 'y') {
            $data['notes'] = 1;
        }
        $data['restbreak'] = 0;
        if (isset($toilet_break_array[$tmp_userID])) {
            $data['restbreak'] = count($toilet_break_array[$tmp_userID]);
        }
        $data['title'] = $title;
        $data['forname'] = $first_names;
        $data['surname'] = $surname;
        $data['endtime'] = $formatted_end_time;
        $data['special'] = '';
        if ($special_needs_extra_time_mins != '') {
            $data['special'] = $special_needs_extra_time_mins;
        }
        $data['specialextra'] = '';
        if ($special_needs_extra_time_mins != '' and $extra_time_mins != '') {
            $data['specialextra'] = ' + ';
        }
        $data['specialextratime'] = '';
        if ($extra_time_mins != '') {
            $data['specialextratime'] = $extra_time_mins;
        }
        $data['accessibility'] = 0;
        if ($student_object['medical'] != '' or $student_object['breaks'] != '') {
            $data['accessibility'] = 1;
        }
        $data['medical'] = '';
        if ($student_object['medical'] != '') {
            $data['medical'] = addslashes($student_object['medical']);
        }
        $data['breaks'] = '';
        if ($student_object['breaks'] != '') {
            $data['breaks'] = addslashes($student_object['breaks']);
        }
        return $data;
    }

    /**
     * Generate the remote student list
     * @param array $student_object
     * @param PaperProperties $property_object
     * @param array $notes_array
     * @param array $toilet_break_array
     * @param bool $allow_timing
     * @throws ErrorException
     * @return array
     */
    public function processStudentList(
        array $student_object,
        PaperProperties $property_object,
        array $notes_array,
        array $toilet_break_array,
        bool $allow_timing
    ): array {
        $data = array();

        $exam_duration_mins = $property_object->get_exam_duration();

        $class = 'student';

        if ($exam_duration_mins == null) {
            throw new ErrorException('Exam duration is mandatory in summative exams');
        }

        if (is_int($exam_duration_mins) === false) {
            throw new ErrorException('$exam_duration_mins ' . $exam_duration_mins . ' must be an integer');
        }

        // Calculate extra time
        $special_needs_extra_time_mins = ($exam_duration_mins / 100) * $student_object['extra_time_percentage'];

        // Get student description
        $tmp_userID = $student_object['user_ID'];
        $surname = $student_object['surname'];
        $first_names = $student_object['first_names'];
        $title = $student_object['title'];
        $paperID = $property_object->get_property_id();

        $data['class'] = $class;
        $data['id'] = $paperID . '_' . $tmp_userID;
        $data['paperid'] = $paperID;
        $data['userid'] = $tmp_userID;
        $data['timing'] = $allow_timing ? 'true' : 'false';
        $data['notes'] = 0;
        $data['completed'] = $this->getCompleted($student_object['user_ID'], $paperID);
        if (isset($notes_array[$tmp_userID]) and $notes_array[$tmp_userID] == 'y') {
            $data['notes'] = 1;
        }
        $data['restbreak'] = 0;
        if (isset($toilet_break_array[$tmp_userID])) {
            $data['restbreak'] = count($toilet_break_array[$tmp_userID]);
        }
        $data['title'] = $title;
        $data['forname'] = $first_names;
        $data['surname'] = $surname;
        $data['endtime'] = '';
        $data['special'] = '';
        if ($special_needs_extra_time_mins != '') {
            $data['special'] = $special_needs_extra_time_mins;
        }
        $data['specialextra'] = '';
        $data['specialextratime'] = '';
        $data['accessibility'] = 0;
        if ($student_object['medical'] != '' or $student_object['breaks'] != '') {
            $data['accessibility'] = 1;
        }
        $data['medical'] = '';
        if ($student_object['medical'] != '') {
            $data['medical'] = addslashes($student_object['medical']);
        }
        $data['breaks'] = '';
        if ($student_object['breaks'] != '') {
            $data['breaks'] = addslashes($student_object['breaks']);
        }
        return $data;
    }
}
