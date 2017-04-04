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
* Internal Review package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Internal review helper class.
 */
 class internalreview {
     
    /*
     * Db connection
     * @var $db
     */
    private $db;
    
    /*
     * Config object
     * @var $config
     */
    private $config;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup() {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    /**
     * Constuctor
     * @param mysqli $db
     */
    function __construct($db) {
        $this->db = $db;
        $this->config = Config::get_instance();
    }
    
    /**
     * Get a list of internal reviews for the current user.
     * @param int $userID  - Question ID of the random question to be loaded.
     * @return array Paper details the current user should review.
     */
    public function get_review_papers($userID) {
        $papers = array();
        $result = $this->db->prepare("SELECT
            paper_title,
            property_id,
            fullscreen,
            DATE_FORMAT(internal_review_deadline, ?) AS internal_review_deadline,
            crypt_name,
            paper_type
            FROM (properties, properties_reviewers)
            WHERE properties.property_id = properties_reviewers.paperID
            AND deleted IS NULL
            AND internal_review_deadline >= CURDATE()
            AND reviewerID = ?
            AND type = 'internal'
            ORDER BY property_id");
        $longdate = $this->config->get('cfg_long_date');
        $result->bind_param('si', $longdate, $userID);
        $result->execute();
        $result->bind_result($paper_title, $property_id, $fullscreen, $internal_review_deadline, $crypt_name, $paper_type);
        $result->store_result();
        while ($result->fetch()) {
            $papers[$property_id] = array('paper_title'=>$paper_title, 'crypt_name'=>$crypt_name, 'fullscreen'=>$fullscreen, 'reviewed'=>'', 'internal_review_deadline'=>$internal_review_deadline, 'type' => $paper_type);
        }
        $result->close();
        $result2 = $this->db->prepare("SELECT paperID,
            DATE_FORMAT(MAX(started), ?) AS started
            FROM review_metadata
            WHERE reviewerID = ?
            GROUP BY paperID ORDER BY paperID");
        $longdatetime = $this->config->get('cfg_long_date_time');
        $result2->bind_param('si', $longdatetime, $userID);
        $result2->execute();
        $result2->bind_result($paperID, $reviewed);
        $result2->store_result();
        while ($result2->fetch()) {
            if (array_key_exists($paperID, $papers)) {
                $papers[$paperID]['reviewed'] = $reviewed;
            }
        }
        $result2->close();
        return $papers;
    }
 }
