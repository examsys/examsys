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
 * Utility class for anomaly related functions.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */
class Anomaly
{
    use ScheduledMail;

    /**
    * Mail type
    */
    private const MAILTYPE = 'anomaly';

    /**
     * Language pack component
     */
    private const LANGCOMPONENT = 'classes/anomaly';

    /** @var int The database id of the anomaly. */
    public int $id;

    /** @var int The type of the anomaly. */
    public int $type;

    /** @var array The data of the anomaly. */
    protected array $data;

    /** @var int The clock type anomaly. */
    public const CLOCK = 1;

    /**
     * Encode anomaly data for the database.
     * @return string
     */
    private function encodeData(): string
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
     * Insert anomaly into database.
     * @throws coding_exception
     * @return array
     */
    public function insert()
    {
        $time = time();
        $stmt = \Config::get_instance()->db->prepare(
            'INSERT anomaly (id, type, time, details, userid, paperid, screen)
            VALUES (NULL, ?, ?, ?, ?, ?, ?)'
        );
        $type = $this->type;
        if (!isset($this->data['userid'])) {
            throw new coding_exception('Anomaly::insert() userid missing');
        }
        if (!isset($this->data['paperid'])) {
            throw new coding_exception('Anomaly::insert() paperid missing');
        }
        if (!isset($this->data['screen'])) {
            throw new coding_exception('Anomaly::insert() screen missing');
        }
        $data = $this->encodeData();
        $stmt->bind_param(
            'iisiii',
            $type,
            $time,
            $data,
            $this->data['userid'],
            $this->data['paperid'],
            $this->data['screen']
        );
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return array('id' => $id, 'timestamp' => $time);
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
     * @param int $papertype The type of paper
     * @param int $startdate datetime to start from
     * @param int $enddate datetime to end at
     * @param bool $studentsonly flag to indicate if only student results required
     * @return array
     */
    public static function getAnomalies(
        int $paperid,
        int $papertype,
        int $startdate,
        int $enddate,
        int $limit,
        int $page,
        bool $studentsonly = true
    ): array {
        $search = new AnomalySearch($papertype);
        $search->setLimit($limit);
        $search->setPage($page);
        $search->setParameters($startdate, $enddate, $paperid, $studentsonly);
        // execute query
        $result = $search->execute();

        $first = $result->first;
        $last = $result->last;
        $pages = $result->pages;
        $stmt = $result->query;

        $stmt->bind_result($userid, $forename, $surname, $sid, $type, $timestamp, $details, $screen);
        $stmt->store_result();

        $anomalies = array (
            'from' => number_format($first),
            'to' => number_format($last),
            'total' => number_format($result->total),
            'pages' => $pages,
            'items' => []
        );
        while ($stmt->fetch()) {
            $anomaly = new \AnomalyItem();
            $anomaly->userid = $userid;
            $anomaly->forename = $forename;
            $anomaly->surname = $surname;
            $anomaly->sid = $sid;
            $anomaly->type = self::getType($type);
            $anomaly->timestamp = date(\Config::get_instance()->get('cfg_very_short_datetime_php'), $timestamp);
            $anomaly->details = json_decode($details, true);
            $anomaly->screen = $screen;
            $anomalies['items'][] = $anomaly;
        }
        $stmt->close();
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

    /**
     * Get the retention period for the data.
     *
     * @return ?int
     */
    public static function getRententionPeriod(): ?int
    {
        return \Retention::getRententionPeriod('anomaly');
    }

    /**
     * Set the retention period
     *
     * @param int $days retention period
     */
    public static function setRetentionPeriod(int $days): void
    {
        \Retention::setRetentionPeriod($days, 'anomaly');
    }

    /**
     * Delete data according to the data retention policy.
     *
     * @return boolean
     */
    public static function deleteDataByRetentionPolicy(): bool
    {
        return \Retention::deleteDataByRetentionPolicy('anomaly');
    }

    /**
     * Check if anomaly detection is enabled for the paper type.
     * @param string $papertype the paper type
     * @return bool
     */
    public static function anomalyDetectionEnabled($papertype): bool
    {
        $anomaly = \Config::get_instance()->get_setting('core', 'paper_anomaly_detection');
        $anomalydetection = false;
        if ($papertype == '1') {
            if ($anomaly['progress'] == 1) {
                $anomalydetection = true;
            }
        }
        if ($papertype == '2') {
            if ($anomaly['summative'] == 1) {
                $anomalydetection = true;
            }
        }
        return $anomalydetection;
    }

    /**
     * Send mail.
     */
    public static function sendMail()
    {
        $now = time();
        $last = self::getLast(self::MAILTYPE);
        if (is_null($last)) {
            $last = 0;
        }
        $langpack = new langpack();
        $sql = 'SELECT a.paperID, p.paper_title
            FROM anomaly a, properties p
            WHERE a.paperID = p.property_id
            AND a.time >= ? and a.time < ?';
        $query = \Config::get_instance()->db->prepare($sql);
        $query->bind_param(
            'ii',
            $last,
            $now
        );
        $query->execute();
        $query->bind_result($paperid, $name);
        $paperlinks = [];
        while ($query->fetch()) {
            $url = \Config::get_instance()->get('cfg_site_address') . '/paper/details.php?paperID=' . $paperid;
            $paperlinks[$name] = $url;
        }
        $query->close();
        if (count($paperlinks) > 0) {
            $render = new render(\Config::get_instance());
            $data['blurb'] = $langpack->get_string(self::LANGCOMPONENT, 'mail_blurb');
            $data['paperlinks'] = $paperlinks;
            $message = $render->renderString($data, 'email/anomaly.html');
            $subject = $langpack->get_string(self::LANGCOMPONENT, 'mail_subject');
            $to = implode(',', \Config::get_instance()->get_setting('core', 'paper_anomaly_email'));
            if (\Mailer::send($to, $subject, $message, '', '', '', '', true)) {
                self::setLast(self::MAILTYPE, $now);
            }
        }
    }
}
