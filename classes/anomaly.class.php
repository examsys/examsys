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
 * Utility class for anomaly related functions.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */
class Anomaly
{
    /** @var int The database id of the anomaly. */
    public int $id;

    /** @var int The type of the anomaly. */
    public int $type;

    /** @var array The data of the anomaly. */
    public array $data;

    /** @var int The clock type anomaly. */
    public const CLOCK = 1;

    /**
     * Anomaly constructor.
     *
     * @param array $data The data.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Encode anomaly data for the database.
     * @return string
     */
    public function encodeData(): string
    {
        return json_encode($this->getData());
    }

    /**
     * Get raw anomaly data.
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function insert()
    {
        $stmt = Config::get_instance()->db->prepare(
            'INSERT anomaly (id, type, timestamp, details, userid, paperid, screen)
            VALUES (NULL, ?, NOW(), ?, ?, ?, ?)'
        );
        $type = $this->type;
        $data = $this->encodeData();
        $stmt->bind_param(
            'isiii',
            $type,
            $data,
            $this->data['userid'],
            $this->data['paperid'],
            $this->data['screen']
        );
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Detect an anomaly.
     */
    public static function detect($data)
    {
        // Intentionally blank.
    }

    /**
     * Get anomalies for the paper
     * @param int $paperid the paper
     * @return array
     */
    public static function getAnomalies(int $paperid)
    {
        $sql = 'SELECT u.id, u.first_names, u.surname, s.student_id, a.type, a.timestamp, a.details, a.screen
            FROM anomaly a JOIN users u ON u.id = a.userID LEFT JOIN sid s ON s.userID = u.id WHERE a.paperID = ?
            ORDER BY a.timestamp DESC';
        $query = Config::get_instance()->db->prepare($sql);
        $query->bind_param('i', $paperid);
        $query->execute();
        $query->bind_result($userid, $forename, $surname, $sid, $type, $timestamp, $details, $screen);

        $anomalies = [];
        while ($query->fetch()) {
            $anomaly = new stdClass();
            $anomaly->userid = $userid;
            $anomaly->forename = $forename;
            $anomaly->surname = $surname;
            $anomaly->sid = $sid;
            $anomaly->type = self::getType($type);
            $anomaly->timestamp = $timestamp;
            $anomaly->details = json_decode($details, true);
            $anomaly->screen = $screen;
            $anomalies[] = $anomaly;
        }
        $query->close();
        return $anomalies;
    }

    /**
     * Get the anomaly type description
     * @param int $type the type
     * @return string
     */
    public static function getType(int $type): string
    {
        if ($type === self::CLOCK) {
            return 'clock';
        } else {
            return 'unknown';
        }
    }
}
