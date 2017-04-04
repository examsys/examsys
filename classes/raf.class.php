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
* Rogo Assessment format for import/export of questions.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
class RAF {

  private $db;
	private $properties;
	private $configObj;
	private $userID;
	private $media = array();
	private $data = array();
	private $zip_filename;
	private $json_filename;
	private $logger;
	private $string;
	private $status_array;
	private $keywords_lookup = null;
	private $raf_company;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  public function __construct($userObject, $configObject, $db, $string) {
    $this->db        	= $db;
		$this->configObj	= $configObject;
		$this->userID			= $userObject->get_user_ID();
		$this->string			= $string;
		$this->notice			= new user_notices;
  }
	
	/**
	 * EXPORT: Creates and outputs a ZIP file containing all the questions on the current paper.
	 * @param array $questions - An array of questions to be exported.
	 */
	public function export($questions) {
		$this->status_array = QuestionStatus::get_all_statuses($this->db, $this->string, true);
		
		$raf_data = $this->create_export_array($questions);
		
		$this->write_file();

		$this->write_zip_file();

		unlink($this->configObj->get('cfg_tmpdir') . $this->userID . '_raf.json');

		$this->zip_filename = $this->userID . '_raf.zip';
		$filepath = $this->configObj->get('cfg_tmpdir');

		// HTTP headers for Zip downloads.
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"" . $this->zip_filename . "\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ". filesize($filepath . $this->zip_filename));
		ob_end_flush();
		@readfile($filepath . $this->zip_filename);

		unlink($filepath . $this->zip_filename);
	}
	
	/**
	 * EXPORT: Create an array of data to export. This will be JSON encoded later.
	 * @param array $questions - An array of questions to be exported.
	 */
	private function create_export_array($questions) {
		$this->data['metadata']['rogo_version']	= $this->configObj->get('rogo_version');
		$this->data['metadata']['export_date']	= date($this->configObj->get('cfg_long_date_php') . ' ' . $this->configObj->get('cfg_long_time_php'));
		$this->data['metadata']['company']			= $this->configObj->get('cfg_company');
		$this->data['items'] = array();
		
		$item_no = 0;

		foreach ($questions as $question) {
			$this->data['items'][$item_no]['question'] = $this->get_question($question);
			
			$this->getImages_from_html($this->data['items'][$item_no]['question']['scenario']);   // Parse out any images in the question scenario.
			$this->getImages_from_html($this->data['items'][$item_no]['question']['leadin']);     // Parse out any images in the question leadin.
			
			$this->data['items'][$item_no]['options'] = $this->get_options($question['q_id']);
			
			$this->data['items'][$item_no]['keywords'] = $this->get_keywords($question['q_id']);

			$item_no++;
		}
	}

	/**
	 * EXPORT: Retrieve a specific question record from the database for export.
	 * @param int $question - ID for a question to be looked up in the database.
	 */
	private function get_question($question) {
		$result = $this->db->prepare("SELECT q_type, theme, scenario, leadin, correct_fback, incorrect_fback, display_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, last_edited, bloom, scenario_plain, leadin_plain, checkout_time, checkout_authorID, deleted, locked, std, status, q_option_order, score_method, settings, guid, title, first_names, surname FROM questions, users WHERE questions.ownerID = users.id AND q_id = ?");
		$result->bind_param('i', $question['q_id']);
		$result->execute();
		$result->bind_result($q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $scenario_plain, $leadin_plain, $checkout_time, $checkout_authorID, $deleted, $locked, $std, $status, $q_option_order, $score_method, $settings, $guid, $title, $first_names, $surname);
		$result->fetch();
		$result->close();
		
		$this->check_media($q_media);
		
		$status = $this->status_array[$status]->get_name();
		
		return array('screen'=>$question['screen'], 'q_id'=>$question['q_id'], 'q_type'=>$q_type, 'theme'=>$theme, 'scenario'=>$scenario, 'leadin'=>$leadin, 'correct_fback'=>$correct_fback, 'incorrect_fback'=>$incorrect_fback, 'display_method'=>$display_method, 'notes'=>$notes, 'ownerID'=>$ownerID, 'q_media'=>$q_media, 'q_media_width'=>$q_media_width, 'q_media_height'=>$q_media_height, 'creation_date'=>$creation_date, 'last_edited'=>$last_edited, 'bloom'=>$bloom, 'scenario_plain'=>$scenario_plain, 'leadin_plain'=>$leadin_plain, 'std'=>$std, 'status'=>$status, 'q_option_order'=>$q_option_order, 'score_method'=>$score_method, 'settings'=>$settings, 'guid'=>$guid, 'owner'=>array('title'=>$title, 'first_names'=>$first_names, 'surname'=>$surname));
	}

	/**
	 * EXPORT: Retrieve a specific option record from the database for export.
	 * @param int $o_id - Option ID to be looked up in the database.
	 */
	private function get_options($o_id) {
		$options = array();

		$result = $this->db->prepare("SELECT option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial FROM options WHERE o_id = ? ORDER BY id_num");
		$result->bind_param('i', $o_id);
		$result->execute();
		$result->bind_result($option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial);
		while ($result->fetch()) {
			$options[] = array('option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'feedback_right'=>$feedback_right, 'feedback_wrong'=>$feedback_wrong, 'correct'=>$correct, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);

			$this->check_media($o_media);
		}
		$result->close();
		
		return $options;
	}
	
	/**
	 * EXPORT: Retrieve keywords (as text) associated with a question for export.
	 * @param int $q_id - ID for a question to be look up keywords.
	 */
	private function get_keywords($q_id) {
		$keywords = array();

		$result = $this->db->prepare("SELECT id, keyword FROM keywords_question, keywords_user WHERE keywords_question.keywordID = keywords_user.id AND q_id = ?");
		$result->bind_param('i', $q_id);
		$result->execute();
		$result->bind_result($id, $keyword);
		while ($result->fetch()) {
			$keywords[$id] = $keyword;
		}
		$result->close();
		
		return $keywords;
	
	}

	/**
	 * EXPORT: Split a give string up and check for media - this checks database fields.
	 */
	private function check_media($media_string) {
		if ($media_string != '') {
			$media_parts = explode('|', $media_string);
			foreach ($media_parts as $media_part) {
				if (trim($media_part) != '') {
					$this->media[] = trim($media_part);
				}
			}	
		}
	}
	
  /**
   * EXPORT: Split a give string up and check for media - this checks HTML within scenario and lead-in fields.
   */
  private function getImages_from_html($html) {
    $mediadirectory = rogo_directory::get_directory('media');
    $regexp = '#' . $mediadirectory->url('(.*)') . '#';
    // Make this a regular expression thar will actually match.
    $regexp = str_replace('&', '&amp;', $regexp);
    $regexp = str_replace('?', '\?', $regexp);

		$parts = explode('<img', $html);
		if (count($parts) > 0) {
			unset($parts[0]);
			foreach ($parts as $image_line) {
				$second_split = explode('src="', $image_line);
				$third_split = explode('"', $second_split[1]);
				$image_src = $third_split[0];
        $matches = array();
        preg_match($regexp, $image_src, $matches);
				if (!empty($matches[1])) {
          $image_src = $matches[1];
        }
				$this->media[] = $image_src;
			}
		}
	}

	/**
	 * EXPORT: Write JSON encoded data out to the filesystem.
	 */
	private function write_file() {
		$file_handle = fopen($this->configObj->get('cfg_tmpdir') . $this->userID . '_raf.json', 'w');
		fwrite($file_handle, json_encode($this->data));
		fclose($file_handle);
	}

	/**
	 * EXPORT: Create a ZIP file and add the JSON data file and all the media that have been found.
	 */
	private function write_zip_file() {
		$zip = new ZipArchive();
		$this->zip_filename = $this->configObj->get('cfg_tmpdir') . $this->userID . '_raf.zip';

		if ($zip->open($this->zip_filename, ZipArchive::CREATE) !== TRUE) {
				exit("cannot open <$this->zip_filename>\n");
		}

		$this->json_filename = $this->configObj->get('cfg_tmpdir') . $this->userID . '_raf.json';

		$zip->addFile($this->json_filename, 'raf.json');
    $mediadirectory = rogo_directory::get_directory('media');
		foreach ($this->media as $media_filename) {
			if (file_exists($mediadirectory->fullpath($media_filename))) {
				$zip->addFile($mediadirectory->fullpath($media_filename), $media_filename);
			}
		}
		$zip->close();
	}
	
	/**
	 * IMPORT: Loads a ZIP file, parses and adds contents to the database.
	 */
	public function import($paperID = 0) {
	  if ($paperID != 0) {
			$this->properties = PaperProperties::get_paper_properties_by_id($paperID, $this->db, $this->string);
		}
		$this->logger = new Logger($this->db);
		$this->status_array = QuestionStatus::get_all_statuses_by_name($this->db, $this->string);
	
	  $this->get_keyword_ids();
		
		$this->zip_filename = $this->userID . '_raf.zip';
		$tmp_path = $this->configObj->get('cfg_tmpdir');
		
		if (!move_uploaded_file($_FILES['raffile']['tmp_name'], $tmp_path . $this->zip_filename))  {
			echo uploadError($_FILES['raffile']['error']);
			exit();
		}

		$dest_dir = $tmp_path . $this->userID;
		if (!file_exists($dest_dir)) {
			mkdir($dest_dir, 0700);
		}
			
		$zip = new ZipArchive;
		if ($zip->open($tmp_path . $this->zip_filename) === TRUE) {
			$zip->extractTo($dest_dir);
			
			if (file_exists($dest_dir . '/raf.json')) {
				$this->data = file_get_contents($dest_dir . '/raf.json');
			} else {
				$zip->close();
				$msg = sprintf($this->string['furtherassistance'], $this->configObj->get('support_email'), $this->configObj->get('support_email'));
				$this->notice->display_notice_and_exit($this->db, $this->string['invalidraf'], $msg, $this->string['invalidraf'], '../artwork/exclamation_48.png', '#C00000', true, true);
			}


			$this->copy_images($dest_dir, $tmp_path);
			
			$this->load_raf_data();
			
			unlink($dest_dir . '/raf.json');

			$zip->close();
		} else {
      $msg = sprintf($this->string['furtherassistance'], $this->configObj->get('support_email'), $this->configObj->get('support_email'));
      $this->notice->display_notice_and_exit($this->db, $this->string['invalidzip'], $msg, $this->string['invalidzip'], '../artwork/exclamation_48.png', '#C00000', true, true);
		}		
	}
	
	/**
	 * IMPORT: Copy a file from the temportary directory to the /media directory.
	 */
	private function copy_images($dir, $tmp_path) {
		$configObj = Config::get_instance();
    $mediadirectory = rogo_directory::get_directory('media');

		if ($handle = opendir($dir)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != '.' and $entry != '..' and $entry != 'raf.json') {
					$new_media = unique_filename($entry);
					rename($tmp_path . $this->userID . '/' . $entry, $mediadirectory->fullpath($new_media));
					$this->data = str_replace($entry, $new_media, $this->data);
				}
			}
			closedir($handle);
		}
	}

	/**
	 * IMPORT: Decode the data file and add questions one by one to the current paper.
	 */
	private function load_raf_data() {
		$data_array = json_decode($this->data, true);
		$display_pos = 1;
		$this->raf_company = $data_array['metadata']['company'];
		foreach ($data_array['items'] as $item) {
			
			$q_id = $this->write_question($item['question']);
			
			$this->write_keywords($item['keywords'], $q_id);
			
			foreach ($item['options'] as $options) {
				$this->write_option($options, $q_id);
			}
			
			if (is_object($this->properties)) {
				$paperID = $this->properties->get_property_id();
				$screen_no = $item['question']['screen'];
				Paper_utils::add_question($paperID, $q_id, $screen_no, $display_pos, $this->db);
			}
			$display_pos++;
		}
	}
	
	/**
	 * IMPORT: If a status (string) does not match the local installations set of statuses,
	 * lookup and return the default status.
	 */
	private function get_default_statusID() {
	  $defaultID = false;
		
	  foreach ($this->status_array as $status) {
		  if ($status->get_is_default()) {
				$defaultID = $status->id;
			}
		}
		
		return $defaultID;
	}

	/**
	 * IMPORT: Insert a single question into the database.
   * @param array $q - Array holding all the information to create the question.
	 */
  private function write_question($q) {
    $configObject = Config::get_instance();
		// Stop SQL errors with ENUM fields and old data which may be blank.
		if ($q['bloom'] == '') 					$q['bloom'] = null;  
		if ($q['q_option_order'] == '') $q['q_option_order'] = 'display order';
		if ($q['score_method'] == '') 	$q['score_method'] = 'Mark per Option';

    // The file may have been written in a version of Rogo that did not use rogo_directory classes.
    // So we must check and filter the leadin and scenario to clean them up.
    //
    // Regular expression to match the old media directory location.
    // it will match directories ./media/ or /media/ and grab the filename.
    $regexp = '#src=".?\/media\/(.*?)"#';
    // The substitution will replace the old src tag with a new one that.
    $webroot = $configObject->get('cfg_root_path');
    // Ensure there is a trailing slash.
    if (substr($webroot, -1) !== '/') {
      $webroot .= '/';
    }
    $substitution = 'src="' . $webroot . 'getfile.php?type=media&amp;filename=$1"';
    // Fix the leadin and scenario.
    $q['leadin'] = preg_replace($regexp, $substitution, $q['leadin']);
    $q['scenario'] = preg_replace($regexp, $substitution, $q['scenario']);

		$server_ipaddress = str_replace('.', '', NetworkUtils::get_server_address());
		$guid = $server_ipaddress . uniqid('', true);

		$status_string = $q['status'];
		if (isset($this->status_array[$status_string])) {
			$q['status'] = $this->status_array[$status_string]->id;  // Translate a name into a number
		} else {
			$defaultID = $this->get_default_statusID();
			if ($defaultID !== false) {
				$q['status'] = $defaultID;
			} else {
				$q['status'] = 1;  // Can't find a valid default, hardwire onto 1.
			}
		}
		
		$result = $this->db->prepare("INSERT INTO questions VALUE (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, NULL, NULL, NULL, NULL, ?, ?, ?, ?, ?, ?)");
		$result->bind_param('ssssssssisssssssissss', $q['q_type'], $q['theme'], $q['scenario'], $q['leadin'], $q['correct_fback'], $q['incorrect_fback'], $q['display_method'], $q['notes'], $this->userID, $q['q_media'], $q['q_media_width'], $q['q_media_height'], $q['bloom'], $q['scenario_plain'], $q['leadin_plain'], $q['std'], $q['status'], $q['q_option_order'], $q['score_method'], $q['settings'], $guid);
		$result->execute();
		$q_id =  $this->db->insert_id;
		$result->close();
		
		
		$date_format = $this->configObj->get('cfg_long_date_php') . ' ' . $this->configObj->get('cfg_short_time_php');
		
		if ($this->raf_company == $this->configObj->get('cfg_company')) {  // The import file company is the same as the current installation. Use the same IDs.
		  $old_q_id = $this->getQID_GUID($q['guid']);
		
		  if ($old_q_id !== false) {
				$this->logger->track_change('Paper', $q_id, $this->userID, $old_q_id, $q_id, 'Add Question (from RAF)');		// Log as a copied file
			}
		} else {
			$this->logger->track_change('Paper', $q_id, $this->userID, '', $q_id, 'Add Question (from RAF)');										// Log as a new file that has been imported
		}
		
		return $q_id;
	}
	
	private function getQID_GUID($guid) {
		$result = $this->db->prepare("SELECT q_id FROM questions WHERE guid = ?");
		$result->bind_param('s', $guid);
		$result->execute();
		$result->bind_result($q_id);
		$result->store_result();
    if ($result->num_rows > 0) {
			$result->fetch();
		} else {
		  $q_id = false;
		}
		$result->close();
		
		return $q_id;
	}
	
	/**
	 * IMPORT: Take the textual keywords of a question, lookup IDs and then insert into the DB.
   * @param array $keywords	- Array of textual keywords
   * @param int $q_id			 	- ID of the question the keywords are for.
	 */
	private function write_keywords($keywords, $q_id) {
		if ($this->raf_company == $this->configObj->get('cfg_company')) {  // The import file company is the same as the current installation. Use the same IDs.
			$keywordIDs = $keywords;
		} else {
			$keywordIDs = array();
			foreach ($keywords as $keyword) {
				if (isset($this->keywords_lookup[$keyword])) {
					$tmp_ID = $this->keywords_lookup[$keyword];
					$keywordIDs[$tmp_ID] = $tmp_ID;
				}
			}
		}
		
		if (count($keywordIDs) > 0) {
			QuestionUtils::add_keywords($keywordIDs, $q_id, $this->db);
		}
	}
	
	/**
	 * IMPORT: Load all personal keywords and keywords belonging to the team(s) of the current paper.
	 * Creates a lookup array for translating the text of a keyword into IDs for insertion into the DB.
	 */
	private function get_keyword_ids() {
	  // Get any personal keywords.
		$result = $this->db->prepare("SELECT id, keyword FROM keywords_user WHERE userID = ? AND keyword_type = 'personal'");
		$result->bind_param('i', $this->userID);
		$result->execute();
		$result->bind_result($id, $keyword);
		while ($result->fetch()) {
		  $this->keywords_lookup[$keyword] = $id;
		}
		$result->close();
		
	  // Get team keywords.
		$modules = array_keys($this->properties->get_modules());

		$result = $this->db->prepare("SELECT id, keyword FROM keywords_user WHERE userID IN (" . implode(',', $modules) . ") AND keyword_type = 'team'");
		$result->execute();
		$result->bind_result($id, $keyword);
		while ($result->fetch()) {
		  $this->keywords_lookup[$keyword] = $id;
		}
		$result->close();	
	}

	/**
	 * IMPORT: Insert a single option into the database.
   * @param array $o 	- Array holding all the information to write into the options table.
   * @param int $q_id - The ID of the question the options belong to.
	 */
	private function write_option($o, $q_id) {
		$result = $this->db->prepare("INSERT INTO options VALUE (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
		$result->bind_param('isssssssddd', $q_id, $o['option_text'], $o['o_media'], $o['o_media_width'], $o['o_media_height'], $o['feedback_right'], $o['feedback_wrong'], $o['correct'], $o['marks_correct'], $o['marks_incorrect'], $o['marks_partial']);
		$result->execute();
		$result->close();
	}	

}
?>
