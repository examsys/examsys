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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once 'add_edit.inc';

function check_fullSave($q_id, $mysqli) {
  if ($_POST['submit'] == 'Limited Save') {
    return false;
  }

  $result = $mysqli->prepare("SELECT locked FROM questions WHERE q_id = ?");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->bind_result($locked);
  $result->fetch();
  $result->close();

  if ($locked == '') {
    return true;
  } else {
    return false;
  }
}

function redirect($userObj, $q_id, $configObj, $db) {
  // Release the users lock.
  if ($_POST['checkout_author'] == $userObj->get_user_ID()) {
    $result = $db->prepare("UPDATE questions SET checkout_time = NULL, checkout_authorID = NULL WHERE q_id = ? AND checkout_authorID = ?");
    $result->bind_param('ii', $q_id, $userObj->get_user_ID());
    $result->execute();
    $result->close();
  }

  if (isset($_SESSION['nav_page'])) {
    $url = $_SESSION['nav_page'] . '?' . $_SESSION['nav_query'];
    $url = preg_replace("/&scrOfY=[0-9]+/", "", $url);

    if (isset($_POST['scrOfY'])) {
      $url .= '&scrOfY=' . $_POST['scrOfY'];
    }
    
    header('location: ' . $url);
  } else {
    header('location: ' . $configObj->get('cfg_root_path') . '/');
  }
  $db->close();
  exit;   // Stop script execution after the redirect.
}

function save_external_responses($dblink, $question, $ids, $actions, $responses, $paper_id = -1) {
  // Record responses to external examiner comments.
  $comments = $question->get_comments($paper_id);
  $changed = false;

  $i = 0;
  foreach ($ids as $id) {
    $action = $actions[$i];
    $response = $responses[$i];

    if ($action != $comments[$id]['action'] or $response != $comments[$id]['response']) {
      $result = $dblink->prepare("UPDATE review_comments SET action = ?, response = ? WHERE id = ?");
      $result->bind_param('ssi', $action, $response, $id);
      $result->execute();
      $result->close();

      $comments[$id]['action'] = $action;
      $comments[$id]['response'] = $response;
      $changed = true;
    }
    $i++;
  }

  if ($changed) $question->set_comments($comments);
}

function check_edit_rights($q_id, $checkout_authorID, $checkout_authorName, $checkout_time, $locked, $dblink, $userObject) {
  global $tmp_ownerID;
  $disabled = '';

  if ($locked != '') {            // Check if locked
    $disabled = 'locked';
    $checkout_author = check_out_paper($dblink, $q_id, $userObject->get_user_ID());
  } else {                              // Check out editing rights against current user
    $checkout_author = check_lock_status($checkout_authorID, $checkout_authorName, $checkout_time, $disabled, $dblink, $q_id, $userObject);
  }

  return $disabled;
}

function check_lock_status($checkout_authorID, $checkout_authorName, $checkout_time, &$disabled, $dblink, $questionID, $userObject) {
  global $string;

  // Check for lockout.
  $current_time = time();
  if ($disabled == '' and $userObject->get_user_ID() != $checkout_authorID) {
    if ($checkout_time != '' and $current_time - $checkout_time < 3600) {
      echo "<script language=\"JavaScript\">\n";
      echo "  alert('" . $string['questionlocked'] . " $checkout_authorName. " . $string['isinreadonly'] . "')";
      echo "</script>\n";
      $disabled = ' disabled';
    } else {
      $checkout_authorID = check_out_paper($dblink, $questionID, $userObject->get_user_ID());
    }
  } elseif ($disabled == '' and $userObject->get_user_ID() == $checkout_authorID) {
    // Set the lock to the current time/author.
    $editLock = $dblink->prepare("UPDATE questions SET checkout_time = NOW(), checkout_authorID = ? WHERE q_id = ?");
    $editLock->bind_param('ii', $userObject->get_user_ID(), $questionID);
    $editLock->execute();
    $editLock->close();
    $checkout_authorID = check_out_paper($dblink, $questionID, $userObject->get_user_ID());
  }

  return $checkout_authorID;
}

/**
 * Set the edit lock to the current time and given author
 * @param object $dblink mysqli link to database
 * @param integer $q_id ID of question to be locked
 * @param integer $user_id ID of the user to assign the lock
 */
function check_out_paper($dblink, $q_id, $user_id) {
  $editLock = $dblink->prepare("UPDATE questions SET checkout_time = NOW(), checkout_authorID = ? WHERE q_id = ?");
  $editLock->bind_param('ii', $user_id, $q_id);
  $editLock->execute();
  $editLock->close();
  return $user_id;
}

function hidden_edit_fields() {
  global $checkout_author;
  $html = '';

  if (isset($_GET['calling'])) {
    $html .= '<input type="hidden" name="calling" value="' . $_GET['calling'] . '" />';
  } else {
    $html .= '<input type="hidden" name="calling" value="" />';
  }

  if (isset($_GET['paperID'])) {
    $html .= '<input type="hidden" name="paperID" value="' . $_GET['paperID'] . '" />';
  } else {
    $html .= '<input type="hidden" name="paperID" value="" />';
  }

  if (isset($_GET['module'])) {
    $html .= '<input type="hidden" name="module" value="' . $_GET['module'] . '" />';
  } else {
    $html .= '<input type="hidden" name="module" value="" />';
  }

  if (isset($_GET['folder'])) {
    $html .= '<input type="hidden" name="folder" value="' . $_GET['folder'] . '" />';
  } else {
    $html .= '<input type="hidden" name="folder" value="" />';
  }

  if (isset($_GET['scrOfY'])) {
    $html .= '<input type="text" name="scrOfY" value="' . $_GET['scrOfY'] . '" />';
  } else {
    $html .= '<input type="hidden" name="scrOfY" value="" />';
  }

  if (isset($_GET['keyword'])) {
    $html .= '<input type="hidden" name="keyword" value="' . str_replace('"', '&quot;', $_GET['keyword']) . '" />';
  } else {
    $html .= '<input type="hidden" name="keyword" value="" />';
  }

  if (isset($_GET['team'])) {
    $html .= '<input type="hidden" name="team" value="' . $_GET['team'] . '" />';
  } else {
    $html .= '<input type="hidden" name="team" value="" />';
  }

  if (isset($checkout_author)) {
    $html .= '<input type="hidden" name="checkout_author" value="' . $checkout_author . '" />';
  } else {
    $html .= '<input type="hidden" name="checkout_author" value="" />';
  }

  return $html;
}

function save_buttons($mode, $disabled, $locked, $allow_correct, $userID, $checkout_author, $paper_id, $paper_count, &$string) {
  $html = '';

  if ($mode == $string['edit']) {
    $save_disabled = '';
    $value = $string['save'];
    $name = 'submit';

    if ($disabled != '') {
      if ($locked != '' or $checkout_author == '') {
        $value = $string['limitedsave'];
      } elseif ($checkout_author != $userID) {
        $save_disabled = ' disabled="disabled"';
        $name = 'bogus';
      }
    }

    $html .= '<input id="submit-save" name="' . $name . '" value="' . $value . '" type="submit" class="ok"' . $save_disabled . ' />';

    if ($allow_correct and $locked != '' and $paper_id != -1) {
      $html .= <<< HTML
      <script>
        var otherSummatives = $paper_count;
        var postExam = true;
        var postExamWarningShown = false;
      </script>

HTML;
    }

  } else {
    $html .= '<input type="submit" id="addbank" name="addbank" value="' . $string['addtobank'] . '" class="ok">';

    if ($paper_id != -1 and substr($paper_id, 0, 5) != 'list:') {
      $html .= '<input type="submit" id="addpaper" name="addpaper" value="' . $string['addtobankandpaper'] . '" class="ok">';
    }
  }

  $html .= '<input id="submit-cancel" name="submit-cancel" value="' . $string['cancel'] . '" type="submit" class="cancel" />';

  return $html;
}

/**
 * Inserts a question into a paper.
 *
 * @param int $p_id The id of the Rogo paper the question should be inserted into.
 * @param int $q_id The id of the question that should be inserted.
 * @param mysqli $db The database connection for Rogo
 * @return int The id of the record that links the question to the paper.
 */
function insert_into_papers($p_id, $q_id, $db) {
  $configObject = Config::get_instance();
  
  $result = $db->prepare("SELECT MAX(screen) AS screen, MAX(display_pos) AS display_pos FROM papers WHERE paper = ?");
  $result->bind_param('i', $p_id);
  $result->execute();
  $result->bind_result($screen, $display_pos);
  $result->fetch();
  $result->close();
  $display_pos++;

  if ($screen == 0) $screen++;

  // Force random mark to recalculate.
  $assessment = new assessment($db, $configObject);
  $update_params = array(
    'random_mark' => array('d', NULL),
    'total_mark' => array('i', NULL)
  );
  $assessment->db_update_assessment($p_id, $update_params);


  if ($result = $db->prepare("INSERT INTO papers VALUES (NULL, ?, ?, ?, ?)")) {
    $result->bind_param('iiii', $p_id, $q_id, $screen, $display_pos);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Add Error", $db->error);
  }
  
  return $db->insert_id;
}

function disable_locked($question, &$dis_class, &$dis_readonly) {
  if ($question->get_locked() != '') {
    $dis_class = ' disabled';
    $dis_readonly = ' readonly="readonly"';
  } else {
    $dis_class = '';
		$dis_readonly = '';
  }
}

/**
 * Create wysiwyg editor or non editable textarea.
 * 
 * @param string $dis_class - name of disabled class
 * @param string $id - name for editor 
 * @param string $field - id for editor
 * @param string $value - current content
 * @param string $type TinyMCE only - type of editor i.e. mceEditorSimple/mceEditor
 * @param int $width width of textarea
 * @param int $height  height of textarea
 * @return html of editor
 */
function wysywig_or_non_editable($dis_class, $id, $field, $value, $type = '', $width=756, $height=220) {
  if ($dis_class == '') {
    $html = wysiwyg_editor($id, $field, $value, $type, $width, $height );
  } else {
    $value_safe = htmlentities(htmlspecialchars_decode($value));
    $html = <<< HTML
  <div class="editor-disabled form-large">
    {$value}&nbsp;
    <input type="hidden" name="{$field}" value="{$value_safe}" />
  </div>
HTML;
  }

  return $html;
}

?>