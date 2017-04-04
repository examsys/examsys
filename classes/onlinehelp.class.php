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
 *
 * Utility class containing a set of generally methods for the online help system.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
Class OnlineHelp {
  private $userObject;
  private $configObject;
  private $string;
  private $notice;
  private $language;
  private $db;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  public function __construct($userObject, $configObject, $string, $notice, $type, $language, $db) {
    $this->userObject   = $userObject;
    $this->configObject = $configObject;
    $this->string       = $string;
    $this->notice       = $notice;
    $this->type         = $type;
    $this->language     = $language;
    $this->db           = $db;
    $this->highlight    = null;
  }
  
  public function set_highlight($highlight) {
    $this->highlight = $highlight;
  }
  
	/**
	 * Display the toolbar at the top of the window.
	 * @param int $id - The ID of the current help page.
	 */
  public function display_toolbar($id) {
    echo "<script>\nvar id = $id;\n</script>\n";
    echo '<form name="myform" action="search.php" method="get" autocomplete="off">';
    echo '<div class="toolbar_buttons"><img src="../back_off.png" title="' . $this->string['back'] . '" alt="' . $this->string['back'] . '" name="back" id="back" class="toolbar_icon" /><img src="../forwards_off.png" title="' . $this->string['forwards'] . '" alt="' . $this->string['forwards'] . '" name="forwards" id="forwards" class="toolbar_icon" /><img src="../home_off.png" title="' . $this->string['home'] . '" alt="' . $this->string['home'] . '" name="home" id="home" class="toolbar_icon" />';
    if ($this->userObject->has_role('SysAdmin')) {
      echo '<img src="../divider.png" class="divider" alt="|" /><img src="../delete_off.png" title="' . $this->string['delete'] . '" alt="' . $this->string['delete'] . '" name="delete" id="delete" class="toolbar_icon" /><img src="../divider.png" class="divider" alt="|" /><img src="../new_off.png" title="' . $this->string['new'] . '" alt="' . $this->string['new'] . '" name="new" id="new" class="toolbar_icon" /><img src="../pointer_off.png" title="' . $this->string['pointer'] . '" alt="' . $this->string['pointer'] . '" name="pointer" id="pointer" class="toolbar_icon" /><img src="../edit_off.png" title="' . $this->string['edit'] . '" alt="' . $this->string['edit'] . '" name="edit" id="edit" class="toolbar_icon" /><img src="../divider.png" class="divider" alt="|" /><img src="../recycle_bin_off.png" title="' . $this->string['recyclebin'] . '" alt="' . $this->string['recyclebin'] . '" name="recycle_bin" id="recycle_bin" class="toolbar_icon" /><img src="../info_off.png" title="' . $this->string['info'] . '" alt="' . $this->string['info'] . '" name="info" id="info" class="toolbar_icon" />';
    }
    if (isset($_GET['searchstring'])) {
      $searchstring = $_GET['searchstring'];
    } else {
      $searchstring = '';
    }
    echo '</div><div class="toolbar_search"><input type="text" id="searchbox" name="searchstring" value="' . $searchstring . '" placeholder="' . $this->string['search'] . '" /><img id="search" src="../search.png" width="16" height="16" title="' . $this->string['search'] . '" alt="' . $this->string['search'] . '" /></div></form>';
  }

	/**
	 * Displays the table of contents.
	 * @param int $pageid - The ID of the current help page - used to highlight current page in TOC.
	 */
  public function display_toc($pageid) {
    if (isset($_GET['scrOfY'])) {
      echo "<script>\nvar scrOfY = " . $_GET['scrOfY'] . ";\n</script>\n";
    } else {
      echo "<script>\nvar scrOfY = 0;\n</script>\n";
    }
    
    if ($this->type == 'student') {
      $sql = 'SELECT articleid, title FROM student_help WHERE id != 1 AND deleted IS NULL AND language = ? ORDER BY title, id';
    } else {
      if ($this->userObject->has_role('SysAdmin')) {
        $sql = 'SELECT articleid, title FROM staff_help WHERE id != 1 AND roles IN ("SysAdmin", "Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
      } elseif ($this->userObject->has_role('Admin')) {
        $sql = 'SELECT articleid, title FROM staff_help WHERE id != 1 AND roles IN ("Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
      } else {
        $sql = 'SELECT articleid, title FROM staff_help WHERE id != 1 AND roles = "Staff" AND deleted IS NULL AND language = ? ORDER BY title, id';
      }
    }

    $sub_section = 0;
    $old_title = '';
    $parent = '';
    $old_parent = '';
    $help_toc = array();
    $help_toc_titles = array();
    
    $help_section = 0;
    $result = $this->db->prepare($sql);
    $result->bind_param('s', $this->language);
    $result->execute();
    $result->bind_result($id, $title);
    while ($result->fetch()) {
      $help_toc[$help_section]['id'] = $id;
      $help_toc[$help_section]['title'] = $title;
      $help_toc_titles[$id] = $title;
      $help_section++;
    }
    $result->close();
    
    $expand_id = 0;
    if ($id !== null) {
      if (isset($help_toc_titles[$pageid])) {
        $slash_pos = strpos($help_toc_titles[$pageid], '/');

        if ($slash_pos !== false) {
          $target_parent = substr($help_toc_titles[$pageid], 0, $slash_pos);


          for ($i=0; $i<$help_section; $i++) {
            if (strpos($help_toc[$i]['title'], $target_parent) === 0 and $expand_id == 0) {
              $expand_id = $help_toc[$i]['id'];
            }
          }
        }
      }
    }
    
    for ($i=0; $i<$help_section; $i++) {
      $id = $help_toc[$i]['id'];
      $slash_pos = strpos($help_toc[$i]['title'], '/');
      if ($slash_pos !== false) {
        $parent = substr($help_toc[$i]['title'], 0, $slash_pos);
        if ($old_parent != '' and $parent != $old_parent) {
          echo "</div>\n";
        }
        $tmp_title = substr($help_toc[$i]['title'], ($slash_pos + 1));

        if ($parent != $old_parent) {
          if ($expand_id == $id) {
            $icon = 'open_book.png';
            echo "<div class=\"book\" id=\"sect$id\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</div>\n";
            echo "<div class=\"open_submenu\" id=\"submenu$id\">";
          } else {
            $icon = 'closed_book.png';
            echo "<div class=\"book\" id=\"sect$id\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</div>\n";
            echo "<div class=\"closed_submenu\" id=\"submenu$id\">";
          }
        }
        $old_parent = $parent;
        $icon = 'single_page.png';      
      } else {
        if ($old_parent != '') {
          echo "</div>\n";
        }
        $tmp_title = $help_toc[$i]['title'];
        $icon = 'single_page.png';
        $parent = '';
        $old_parent = $parent;
      }
      if ($id == $pageid) {
        echo "<div id=\"title$id\" class=\"page\" style=\"font-weight:bold\"><img src=\"../$icon\" class=\"icon16_active\" />$tmp_title</div>\n";
      } else {
        echo "<div id=\"title$id\" class=\"page\"><img src=\"../$icon\" class=\"icon16_active\" />$tmp_title</div>\n";
      }
    }

    if ($old_parent != '') echo "</div>\n";
  }  

	/**
	 * Loads details of a help page and returns them in an array.
	 * @param int $articleid - The ID of the help page to return.
   * @return array of page details.
	 */
  public function get_page_details($articleid) {
    if ($this->type == 'student') {
      $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, NULL AS roles FROM student_help WHERE articleid = ? AND language = ? AND deleted IS NULL LIMIT 1';
    } else {
      if ($this->userObject->has_role('SysAdmin')) {
        $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, roles FROM staff_help WHERE articleid = ? AND language = ? AND roles IN ("SysAdmin", "Admin", "Staff") AND deleted IS NULL LIMIT 1';
      } elseif ($this->userObject->has_role('Admin')) {
        $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, roles FROM staff_help WHERE articleid = ? AND language = ? AND roles IN ("Admin", "Staff") AND deleted IS NULL LIMIT 1';
      } else {
        $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, roles FROM staff_help WHERE articleid = ? AND language = ? AND roles = "Staff" AND deleted IS NULL LIMIT 1';
      }
    }    
    $results = $this->db->prepare($sql);
    $results->bind_param('is', $articleid, $this->language);
    $results->execute();
    $results->store_result();
    
    $results->bind_result($id, $title, $body, $page_type, $checkout_time, $checkout_authorID, $roles);
    $results->fetch();
    $row_no = $results->num_rows;
    $results->close();
    
    if ($row_no == 0) {
      return false;
    }
    
    return array('id'=>$id, 'title'=>$title, 'body'=>$body, 'page_type'=>$page_type, 'checkout_time'=>$checkout_time, 'checkout_authorID'=>$checkout_authorID, 'roles'=>$roles);
  }
  
	/**
	 * Saves a staff help page back to the database.
	 * @param string $title     - The title of the help page.
	 * @param string $body      - The main contents of the help page.
	 * @param string $roles     - The roles of users allowed to view the page.
	 * @param string $articleid - The ID of the help page being saved.
	 * @param string $pointerid - The ID of the pointer page.
	 */
  private function save_staff_page($title, $body, $roles, $articleid, $pointerid) {
    $body_plain = strip_tags($body);
    
    if ($articleid == $pointerid) {
      // Editing normal page.
      $result = $this->db->prepare("UPDATE staff_help SET title = ?, body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL, roles = ? WHERE articleid = ? AND language = ?");
      $result->bind_param('ssssis', $title, $body, $body_plain, $roles, $articleid, $this->language);
      $result->execute();
      $result->close();
    } else {
      // Editing a page pointed to.
      $result = $this->db->prepare("UPDATE staff_help SET title = ? WHERE articleid = ? AND language = ?");
      $result->bind_param('sis', $title, $articleid, $this->language);
      $result->execute();
      $result->close();

      $result = $this->db->prepare("UPDATE staff_help SET body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL, roles = ? WHERE articleid = ? AND language = ?");
      $result->bind_param('sssis', $body, $body_plain, $roles, $pointerid, $this->language);
      $result->execute();
      $result->close();
    }    
  }
  
	/**
	 * Saves a student help page back to the database.
	 * @param string $title     - The title of the help page.
	 * @param string $body      - The main contents of the help page.
	 * @param string $articleid - The ID of the help page being saved.
	 * @param string $pointerid - The ID of the pointer page.
	 */
  private function save_student_page($title, $body, $articleid, $pointerid) {
    $body_plain = strip_tags($body);
    if ($articleid == $pointerid) {
      // Editing normal page.
      $result = $this->db->prepare("UPDATE student_help SET title = ?, body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL WHERE articleid = ? AND language = ?");
      $result->bind_param('sssis', $title, $body, $body_plain, $articleid, $this->language);
      $result->execute();
      $result->close();
    } else {
      // Editing a page pointed to.
      $result = $this->db->prepare("UPDATE student_help SET title = ? WHERE articleid = ? AND language = ?");
      $result->bind_param('sis', $title, $articleid, $this->language);
      $result->execute();
      $result->close();

      $result = $this->db->prepare("UPDATE student_help SET body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL WHERE articleid = ? AND language = ?");
      $result->bind_param('ssis', $body, $body_plain, $pointerid, $this->language);
      $result->execute();
      $result->close();
    }
  }
  
	/**
	 * Saves a help page back to the database.
	 * @param string $title     - The title of the help page.
	 * @param string $body      - The main contents of the help page.
	 * @param string $roles     - The roles of users allowed to view the page.
   * @param string $articleid - The ID of the help page being saved.
	 * @param string $pointerid - The ID of the pointer page.
	 */
  public function save_page($title, $body, $roles, $articleid, $pointerid) {
    $articleid = (int)$articleid;
    $pointerid = (int)$pointerid;
    
    if ($this->type == 'student') {
      $this->save_student_page($title, $body, $articleid, $pointerid);
    } else {
      $this->save_staff_page($title, $body, $roles, $articleid, $pointerid);
    }
  }
  
	/**
	 * Displays the contents of a help folder.
	 * @param string $folder - The name of the folder to display.
	 */
  public function display_folder($folder) {
    $t = $folder . '/%';
    
    $sql = 'SELECT articleid, title FROM ' . $this->type . '_help WHERE title LIKE ? AND language = ? ORDER BY title';
    
    $result = $this->db->prepare($sql);
    $result->bind_param('ss', $t, $this->language);
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $title);

    echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#295AAD\">$folder</div>\n";

    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n<tr><td style=\"width:20px\">&nbsp;</td><td>";

    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
    echo "<tr><td style=\"background-color: #295AAD; color:white; font-weight:bold\">&nbsp;&nbsp;" . $this->string['topics'] . "</td><td style=\"background-color: #295AAD; color:white; text-align:right\">" . $result->num_rows . "&nbsp;" . $this->string['items'] . "&nbsp;</td></tr>";
    echo "</table>\n";

    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
    $row_no = 0;
    while ($result->fetch()) {
      $row_no++;
      echo "<tr><td style=\"width:24px\" class=\"row\"><img src=\"../single_page.png\" class=\"icon16_active\" /></td><td class=\"row\"><a href=\"index.php?id=$id\">" . str_replace($folder . '/', '', $title) . "</a></td></tr>\n";
    }
    $result->close();
    echo "</table>\n</td><td style=\"width:20px\">&nbsp;</td></tr>\n</table>\n";    
  }

	/**
	 * Displays a specific help page.
	 * @param int $id - The ID of the folder to display.
	 */
  public function display_page($id) {
    $page_details = $this->get_page_details($id);
    $original_title = $page_details['title'];
    
    if ($page_details['page_type'] == 'pointer') {    // If pointer look up source page.
      $page_details = $this->get_page_details($page_details['body']);
      $page_details['title'] = $original_title;   // Set the title back to the pointer title.
    }    

    if ($page_details['body'] == '' and $page_details['title'] == '') {
      $msg = sprintf($this->string['furtherassistance'], $this->configObject->get('support_email'), $this->configObject->get('support_email'));
      $this->notice->display_notice_and_exit($this->db, $this->string['pagenotfound'], $msg, $this->string['pagenotfound'], '/artwork/page_not_found.png', '#C00000');
    }

    $this->display_header($id, $page_details['title']);

    // Perform replacement on certain strings.
    $page_details['body'] = str_replace('$support_email', '<a href="mailto:' . $this->configObject->get('support_email') . '">' . $this->configObject->get('support_email') . '</a>', $page_details['body']);
    $page_details['body'] = str_replace('$local_server', NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'], $page_details['body']);

    $offset = 0;
    if ($this->highlight !== null) {
      do {
        $found = stripos($page_details['body'], $this->highlight, $offset);
        if ($found !== false) {
          $first_part = substr($page_details['body'], 0 , $found);
          $open_bracket = strrpos($first_part, '<');
          $close_bracket = strrpos($first_part, '>');
          if (($open_bracket < $found and $found < $close_bracket) or ($close_bracket < $open_bracket)) {
            $offset = $found + strlen($this->highlight);
          } else {
            $page_details['body'] = substr($page_details['body'], 0, $found) . '<span style="background-color:#FFFF00">' . substr($page_details['body'], $found, strlen($this->highlight)) . '</span>' . substr($page_details['body'], $found + strlen($this->highlight));
            $offset = $found + 48;
          }
        }
      } while ($found !== false);
    }
    echo $page_details['body'];
    $this->display_footer($id);
    
    $this->record_in_log($id);
  }

	/**
	 * Displays a header for a help page.
	 * @param int $id       - The ID of the help page to display.
	 * @param string $title - The title of the help page.
	 */
  private function display_header($id, $title) {
    if ($id == 1) {
      // ID 1 is for the homepage.
      echo "<div>\n";
    } else {
      echo "<div class=\"help_title\">" . str_replace('/', ': ', $title) . "<img class=\"flag\" src=\"../" . $this->language . ".png\" /></div>\n";
      echo "<div style=\"margin-left:20px; margin-right:20px\">\n";
    }    
  }
  
	/**
	 * Displays a footer for a help page.
	 * @param int $id - The ID of the help page to display.
	 */
  private function display_footer($id) {
    if ($id > 1) {    // Do not display footer if ID is one.
      echo "<div class=\"footer_line\"></div>\n";
      echo "<div class=\"footer_left gototop\"><img src=\"../../artwork/top_icon.gif\" width=\"9\" height=\"12\" />&nbsp;" . $this->string['top'] . "</div>\n";
      if ($this->userObject->has_role('SysAdmin')) {
        echo '<div class="footer_right">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $this->configObject->get('cfg_root_path') . '/help/staff/index.php?id=' . $id . '</div>';
      }
    }
  }
  
	/**
	 * Record a page hit in the log table.
	 * @param int $id - The ID of the help page to log.
	 */
  private function record_in_log($id) {
    if ($id != '1' and !$this->userObject->has_role('SysAdmin')) {   // Don't record the homepage or SysAdmin activities.
      $sql = "INSERT INTO help_log VALUES (NULL, '" . $this->type . "', ?, NOW(), ?)";

      $result = $this->db->prepare($sql);
      $result->bind_param('ii', $this->userObject->get_user_ID(), $id);
      $result->execute();  
      $result->close();
    }
  }
  
	/**
	 * Record a search in the search log.
	 * @param string $searchstring - The search string used.
	 * @param int $total_hits      - The number of hits found.
	 */
  private function record_in_search_log($searchstring, $total_hits) {
    $sql = "INSERT INTO help_searches VALUES (NULL, '" . $this->type . "', ?, NOW(), ?, ?)";
    
    $result = $this->db->prepare($sql);
    $result->bind_param('isi', $this->userObject->get_user_ID(), $searchstring, $total_hits);
    $result->execute();  
    $result->close();
  }
  
	/**
	 * Creates a new help page in the database.
	 * @param string $title - The title of the new help page.
	 * @param string $body  - The main content of the help page.
	 * @param string $roles - Which roles are allowed to view the page.
   * @return int the ID of the newly created page.
	 */
  public function create_page($title, $body, $roles = '') {
    $body_plain = strip_tags($body);

    if ($this->type == 'student') {
      $result = $this->db->prepare("INSERT INTO student_help VALUES (NULL, ?, ?, ?, 'page', NULL, NULL, NULL, ?, 0, '0000-00-00 00:00:00')");
      $result->bind_param('ssss', $title, $body, $body_plain, $this->language);
    } else {
      $result = $this->db->prepare("INSERT INTO staff_help VALUES (NULL, ?, ?, ?, 'page', NULL, NULL, ?, NULL, ?, 0, '0000-00-00 00:00:00')");
      $result->bind_param('sssss', $title, $body, $body_plain, $_POST['page_roles'], $this->language);
    }
    $result->execute();  
    $result->close();
    
    $articleid = $this->db->insert_id;
    
    // Update the articleid to match the new id field.
    $result = $this->db->prepare("UPDATE " . $this->type . "_help SET articleid = ? WHERE id = ?");
    $result->bind_param('ii', $articleid, $articleid);
    $result->execute();  
    $result->close();  
    
    return $articleid;
  }
  
	/**
	 * Creates a new help pointer in the database.
	 * @param string $title   - The title of the new pointer page.
	 * @param string $pageID  - The ID of the page to point to.
   * @return int the ID of the newly created pointer page.
	 */
  public function create_pointer($title, $pageID) {
    if ($this->type == 'student') {
      $result = $this->db->prepare("INSERT INTO student_help VALUES (NULL, ?, ?, NULL, 'pointer', NULL, NULL, NULL, '" . $this->language . "', 0, '0000-00-00 00:00:00')");
      $result->bind_param('ss', $title, $pageID);
    } else {
      $result = $this->db->prepare("INSERT INTO staff_help VALUES (NULL, ?, ?, NULL, 'pointer', NULL, NULL, 'Staff', NULL, '" . $this->language . "', 0, '0000-00-00 00:00:00')");
      $result->bind_param('ss', $title, $pageID);
    }
    $result->execute();  
    $result->close();
    
    $articleid = $this->db->insert_id;
    
    // Update the articleid to match the new id field.
    $result = $this->db->prepare("UPDATE " . $this->type . "_help SET articleid = ? WHERE id = ?");
    $result->bind_param('ii', $articleid, $articleid);
    $result->execute();  
    $result->close();
    
    return $articleid;
  }
  
	/**
	 * Sets an edit lock on a page.
	 * @param int $articleid - The ID of the help page to lock.
	 */
  public function set_edit_lock($articleid) {
    $sql = 'UPDATE ' . $this->type . '_help SET checkout_time = NOW(), checkout_authorID = ? WHERE articleid = ? AND language = ?';

    $result = $this->db->prepare($sql);
    $result->bind_param('iis', $this->userObject->get_user_ID(), $articleid, $this->language);
    $result->execute();
    $result->close();
  }
  
	/**
	 * Releases an edit lock on a page.
	 * @param int $articleid - The ID of the help page to lock.
	 */
  public function release_edit_lock($articleid) {
    $sql = 'UPDATE ' . $this->type . '_help SET checkout_time = NULL, checkout_authorID = NULL WHERE articleid = ? AND language = ?';

    $result = $this->db->prepare($sql);
    $result->bind_param('is', $articleid, $this->language);
    $result->execute();
    $result->close();
  }
  
	/**
	 * Sets a specified help page ID to be deleted.
	 * @param int $pageID - The ID of the help page to delete.
	 */
  private function delete_id($pageID) {
    $sql = 'UPDATE ' . $this->type . '_help SET deleted = NOW() WHERE articleid = ? AND language = ?';
    
    $deleteQuery = $this->db->prepare($sql);
    $deleteQuery->bind_param('is', $pageID, $this->language);
    $deleteQuery->execute();
    $deleteQuery->close();  
  }
  
	/**
	 * Deletes a specific help page.
	 * @param int $originalID - The ID of the help page or pointer to delete.
	 */
  public function delete_page($originalID) {
    $page_details = $this->get_page_details($originalID);

    if ($page_details['page_type'] == 'page') {
      // Search for any pointers to the current page.
      $sql = "SELECT articleid, body FROM " . $this->type . "_help WHERE type = 'pointer' AND articleid != ? AND body = ? AND language = ?";
      
      $result = $this->db->prepare($sql);
      $result->bind_param('iis', $originalID, $originalID, $this->language);
      $result->execute();
      $result->store_result();
      $result->bind_result($page_id, $body);
      while ($result->fetch()) {
        $this->delete_id($page_id);     // Delete the pointer page.
      }
      $result->close();
    }

    $this->delete_id($originalID);      // Delete the original page.
  }
  
	/**
	 * Restores a deleted help page.
	 * @param int $pageID - The ID of the help page to restore.
	 */
  public function restore_page($pageID) {
    $sql = 'UPDATE ' . $this->type . '_help SET deleted = NULL WHERE articleid = ? AND language = ?';
    
    $restore = $this->db->prepare($sql);
    $restore->bind_param('is', $pageID, $this->language);
    $restore->execute();
    $restore->close();
  }
  
	/**
	 * Performs a full-text search on the help database.
	 * @param string $searchstring - The search term to use for the search.
   * @return array of search results.
	 */
  public function find($searchstring) {
    $search_results = array();

    if ($this->type == 'student') {
      // Check if the database is using a non-standard table type that does not support full text indexing.
      if (DBUtils::supports_fulltext_search('student_help')) {
        $searchtext_student = "MATCH (title, body_plain) AGAINST (?) AS relevance FROM student_help WHERE MATCH (title, body_plain) AGAINST (? IN BOOLEAN MODE)";
      } else {
        $searchstring = '%' . $searchstring . '%';
        // Relevance has no meaning here so just set to 1.0 for all.
        $searchtext_student = "1.0 AS relevance FROM student_help WHERE (LOWER(title) LIKE LOWER(?) OR LOWER(body_plain) LIKE LOWER(?))";
      }
      $result = $this->db->prepare("SELECT articleid, title, " . $searchtext_student . " AND deleted IS NULL AND language = ? ORDER BY relevance DESC");
    } else {
      // Check if the database is using a non-standard table type that does not support full text indexing.
      if (DBUtils::supports_fulltext_search('staff_help')) {
        $searchtext_staff = "MATCH (title, body_plain) AGAINST (?) AS relevance FROM staff_help WHERE MATCH (title, body_plain) AGAINST (? IN BOOLEAN MODE)";
      } else {
        $searchstring = '%' . $searchstring . '%';
        // Relevance has no meaning here so just set to 1.0 for all.
        $searchtext_staff = "1.0 AS relevance FROM staff_help WHERE (LOWER(title) LIKE LOWER(?) OR LOWER(body_plain) LIKE LOWER(?))";
      }
      // Return only types of pages related to the type of staff.
      if ($this->userObject->has_role('SysAdmin')) {
        $roles_check = 'AND roles IN ("SysAdmin","Admin","Staff")';
      } elseif ($this->userObject->has_role('Admin')) {
        $roles_check = 'AND roles IN ("Admin","Staff")';
      } else {
        $roles_check = 'AND roles="Staff"';
      }      
      $result = $this->db->prepare("SELECT articleid, title, " . $searchtext_staff . " $roles_check AND deleted IS NULL AND language = ? ORDER BY relevance DESC");
    }
    $result->bind_param('sss', $searchstring, $searchstring, $this->language);
    $result->execute();
    $result->bind_result($id, $title, $score);
    while ($result->fetch()) {
      $search_results[] = array('id'=>$id, 'title'=>$title, 'score'=>$score);
    }
    $result->close();
    
    // Log the search in the database.
    if (!$this->userObject->has_role('SysAdmin')) {   // Don't record SysAdmin searches.
      $total_hits = count($search_results);
      $this->record_in_search_log($searchstring, $total_hits);
    }
    
    return $search_results;
  }
}
