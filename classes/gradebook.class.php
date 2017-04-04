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
* Gradebook package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Gradebook helper class.
 */
class gradebook {
    
    /**
     * The db connection
     */
    private $db;
    
    /**
     * External paper - externalid used to referece paper
     * @var string
     */
    const EXTPAPER = 'extpaper';
    
    /**
     * External module - externalid used to referece module
     * @var string
     */
    const EXTMODULE = 'extmodule';
    
    /**
     * Internal paper - rogo id used to referece paper
     * @var string
     */
    const PAPER = 'paper';
    
    /**
     * Internal module - rogo id used to referece module
     * @var string
     */
    const MODULE = 'module';

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
     * Constructor
     * @param object $db
     * @return void 
     */
    function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Check if the paper has been graded.
     * @param integer $paperid 
     * @return bool true if already graded
     */
    public function paper_graded($paperid) {
        $result = $this->db->prepare("SELECT count(paperid) FROM gradebook_paper WHERE paperid = ?");
        $result->bind_param('i', $paperid);
        $result->execute();
        $result->bind_result($count);
        $result->fetch();
        $result->close();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Store grade in gradebook.
     * @param integer $userid 
     * @param integer $paperid 
     * @param integer $grade - raw grade
     * @param double $adjusted - adjusted grade
     * @param integer $classification 
     * @return bool true if grade added to gradebook
     */
    public function store_grade($userid, $paperid, $grade, $adjusted, $classification) {
        
        $student = \UserUtils::has_user_role($userid, 'Student', $this->db);
        if ($student) {
            $sqluser = $this->db->prepare("INSERT INTO gradebook_user (paperid, userid, raw_grade, adjusted_grade, classification) VALUES (?, ?, ?, ?, ?)");
            $sqluser->bind_param('iiids', $paperid, $userid, $grade, $adjusted, $classification);
            $sqluser->execute();
            $sqluser->close();
            if ($this->db->errno != 0) {
                return false;
            }
            return true;
        } else {
            return false;
        }
       
    }
    
    /**
     * Create a gradebook for the paper
     * @param integer $paperid 
     * @return bool true if created 
     */
    public function create_gradebook($paperid) {
        if (!$this->paper_graded($paperid)) {
            $sqlpaper = $this->db->prepare("INSERT INTO gradebook_paper (paperid) VALUES (?)");
            $sqlpaper->bind_param('i', $paperid);
            $sqlpaper->execute();
            $sqlpaper->close();
            if ($this->db->errno != 0) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Get the gradebook for a paper
     * @param string $paperidtype type of id to serach on
     * @param int $paperid id to search with
     * @return array|bool gradebook for paper or false  
     */
    public function get_paper_gradebook($paperidtype, $paperid) {
        if ($paperidtype == self::EXTPAPER) {
            $pid = \Paper_utils::get_id_from_externalid($paperid, $this->db);
        } else {
            $pid = $paperid;
        }
        
        if ($this->paper_graded($pid)) {
            $sql = $this->db->prepare("SELECT gu.userid, s.student_id, u.username, gu.raw_grade, ROUND(gu.adjusted_grade, 2), gu.classification FROM
                gradebook_paper p, gradebook_user gu, users u, sid s WHERE p.paperid = gu.paperid AND u.id = gu.userid AND u.id = s.userID AND p.paperid = ?");
            $sql->bind_param('i', $pid);
            $sql->execute();
            $sql->bind_result($userid, $studentid, $username, $raw_grade, $adjusted_grade, $classification);
            $users = array();
            while ($sql->fetch()) {
                if ($paperidtype == self::EXTPAPER) {
                    $uid = $studentid;
                } else {
                    $uid = $userid;
                }
                $users[$uid] = array('raw_grade' => $raw_grade, 'adjusted_grade' => $adjusted_grade,
                    'classification' => $classification, 'username' => $username);
            }
            $gradebook[$paperid] = $users;
            $sql->close();
            return $gradebook;
        } else {
            return false;
        }
    }
    
    /**
     * Get a gradebook for a paper with more user data than the default gradebook
     * @param int $paperid id to search with
     * @return array|bool detailed gradebook for paper or false  
     */
    public function get_user_detailed_paper_gradebook($paperid) {
        if ($this->paper_graded($paperid)) {
            $sql = $this->db->prepare("SELECT gu.userid, s.student_id, u.username, u.surname, u.first_names, gu.raw_grade, ROUND(gu.adjusted_grade, 2), gu.classification FROM
                gradebook_paper p, gradebook_user gu, users u, sid s WHERE p.paperid = gu.paperid AND u.id = gu.userid AND u.id = s.userID AND p.paperid = ?");
            $sql->bind_param('i', $paperid);
            $sql->execute();
            $sql->bind_result($userid, $studentid, $username, $surname, $first_names, $raw_grade, $adjusted_grade, $classification);
            $users = array();
            while ($sql->fetch()) {
                $users[$userid] = array('student_id' => $studentid, 'raw_grade' => $raw_grade, 'adjusted_grade' => $adjusted_grade,
                    'classification' => $classification, 'username' => $username, 'surname' => $surname, 'first_names' => $first_names);
            }
            $gradebook[$paperid] = $users;
            $sql->close();
            return $gradebook;
        } else {
            return false;
        }
    }
    
    /**
     * Get the gradebook for a module
     * @param string $moduleidtype type of id to serach on
     * @param int $moduleid id to search with
     * @return array|bool gradebook for module or false
     */
    public function get_module_gradebook($moduleidtype, $moduleid) {
        if ($moduleidtype == self::EXTMODULE) {
            $modid = \module_utils::get_id_from_externalid($moduleid, $this->db);
        } else {
            $modid = $moduleid;
        }
        
        $sql = $this->db->prepare("SELECT
            p.paperid, pr.externalid, gu.userid, s.student_id, u.username, gu.raw_grade, ROUND(gu.adjusted_grade, 2), gu.classification
            FROM
                gradebook_paper p, 
                gradebook_user gu, 
                users u,
                sid s,
                properties_modules m,
                properties pr
            WHERE
                m.property_id = p.paperid AND
                pr.property_id = p.paperid AND
                p.paperid = gu.paperid AND 
                u.id = gu.userid AND
                u.id = s.userID  AND
                m.idMod = ?");
        $sql->bind_param('i', $modid);
        $sql->execute();
        $sql->bind_result($paperid, $extpaperid, $userid, $studentid, $username, $raw_grade, $adjusted_grade, $classification);
        $papers = array();
        while ($sql->fetch()) {
            $users = array('raw_grade' => $raw_grade, 'adjusted_grade' => $adjusted_grade, 'classification' => $classification,
                'username' => $username);
            if ($moduleidtype == self::EXTMODULE) {
                $papers[$extpaperid][$studentid] = $users;
            } else {
                $papers[$paperid][$userid] = $users;    
            }
        }
        $sql->close();
        $gradebook[$moduleid] = $papers;
        if (count($gradebook[$moduleid]) > 0) {
            return $gradebook;
        } else {
            return false;
        }
    }
}
