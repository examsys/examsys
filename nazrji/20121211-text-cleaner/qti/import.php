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


require_once 'include/inc.php';
require_once 'qti/qti_load.php';
require_once 'qti12/qti12_load.php';
require_once 'qti20/qti20_load.php';
require_once 'local/local_save.php';

$max_screen = 0;

//$stmt = $mysqli->prepare("SELECT paper_title, moduleID, folder, paper_ownerID, moduleID, DATE_FORMAT(start_date,'%Y%m%d%H%i%S') AS start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i%S') AS end_date, DATE_FORMAT(created,'%Y%m%d%H%i%S') AS created, MAX(screen) AS screen, fullscreen, MAX(display_pos) AS display_pos, paper_type, externals, internal_reviewers, labs, calendar_year, exam_duration, crypt_name, display_question_mark FROM properties LEFT JOIN papers ON properties.property_id=papers.paper WHERE property_id=? GROUP BY paper_title");
$stmt = $mysqli->prepare("SELECT paper_title, moduleID, folder, paper_ownerID, moduleID, DATE_FORMAT(start_date,'%Y%m%d%H%i%S') AS start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i%S') AS end_date, DATE_FORMAT(created,'%Y%m%d%H%i%S') AS created, MAX(screen) AS screen, fullscreen, MAX(display_pos) AS display_pos, paper_type, externals, internal_reviewers, labs, calendar_year, exam_duration, crypt_name, display_question_mark FROM properties_modules, modules, properties LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.property_id = properties_modules.property_id AND properties_modules.idMod = modules.id AND properties.property_id=? GROUP BY paper_title");

$stmt->bind_param('i', $paperID);
$stmt->execute();
$stmt->bind_result($paper_title, $paper_moduleID, $tmp_folder, $paper_ownerID, $tmp_module, $start_date, $end_date, $created, $max_screen, $fullscreen, $max_display_pos, $paper_type, $externals, $internal_reviewers, $labs, $session, $exam_duration, $crypt_name, $display_question_mark);
while ($stmt->fetch()) {

  if (date("YmdHis", time()) >= $start_date and date("YmdHis", time()) <= $end_date) {
    $active_date = 1;
  } else {
    $active_date = 0;
  }
  if (date("YmdHis", time()) >= $start_date and $start_date != '' and $paper_type == '2') {
    $summative_lock = 1;
  } else {
    $summative_lock = 0;
  }
}
$stmt->close();
$moduleID = $paper_moduleID;

// get some paper details
$paper = GetVar("paperID");
$paperID = $paper;
$db = new Database();
$db->SetTable('properties');
$db->AddField('UNIX_TIMESTAMP(start_date) AS start_date');
$db->AddField('paper_title');
$db->AddField('paper_type');
$db->AddWhere('property_id', $paper, 'i');
$paper_row = $db->GetSingleRow();

if ($paper_row['paper_type'] == '2' and time() > $paper_row['start_date'] and $paper_row['start_date'] != '') {
  $summative_lock = 1;
  include "tmpl/import_locked.php";
  exit;
} else {
  $summative_lock = 0;
}

if (!array_key_exists('file', $_FILES)) {
  include "tmpl/import_file.php";
  exit;
}

$show_debug = IsAdminUser($userObject->get_user_ID()); //todo replace with userobject function

// create dir for qti to save into
$base_dir = $cfg_web_root.'qti/imports/';
$dir = GetAuthorName($userObject->get_user_ID())."/".date("Y-m-d")."/".date("H.i.s"); //todo replace with userobject function

if (!file_exists($base_dir.$dir)) mkdir($base_dir.$dir, 0755, true);
$save_params = new stdClass();
$save_params->dir = $dir;
$save_params->base_dir = $base_dir;

global $load_params;
$load_params = new stdClass();
$load_params->dir = $dir;
$load_params->base_dir = $base_dir;

// upload file
$ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
$file = $base_dir.$dir."/import.".$ext;
move_uploaded_file($_FILES["file"]["tmp_name"], $file);

// set up qti load classes
$import = new IE_QTI_Load();
$load_params->sourcefile = $file;
$load_params->original_filename = $_FILES["file"]["name"];

// setup touchstone save classes
$export = new IE_Local_Save();
$save_params->paper = $paper;
$save_params->sourcefile = $file;
$save_params->original_filename = $_FILES["file"]["name"];

// perform operation
$result = array();
$general_params = new stdClass();
$result['general']['params'] = $general_params;

// call import with imptype and params
$ob = new OB();
$ob->ClearAndSave();
$data = $import->Load($load_params);
$result['load']['type'] = "qti12";
$result['load']['params'] = $load_params;
$result['load']['debug'] = $ob->GetContent();
$result['load']['warnings'] = $import->warnings;
$result['load']['errors'] = $import->errors;
$result['load']['data'] = $data;
$ob->Restore();

// create object with save source
$ob = new OB();
$ob->ClearAndSave();
$export->Save($save_params, $data);
$result['save']['type'] = 'touchstone';
$result['save']['params'] = $save_params;
$result['save']['debug'] = $ob->GetContent();
$result['save']['warnings'] = $export->warnings;
$result['save']['errors'] = $export->errors;
$result['save']['data'] = $data;
$ob->Restore();

/////////////////////////
// STORE RESULTS STUFF //
/////////////////////////

// display result page
include "tmpl/import_main.php";
$mainoutput = $ob->GetContent();

// store page that was presented to the user
$ob->Clear();
include "tmpl/import_details.php";
$result_debug_file = $base_dir.$dir."/result.html";
$output = $ob->GetContent();
//$ob->Restore();
file_put_contents($result_debug_file, $output);

///////////////////////
// DEBUG INFORMATION //
///////////////////////

// store intermediate format debug information
$load_debug_file = $base_dir.$dir."/debug_int.html";
$ob->Clear();
include "tmpl/debug_head.php";
print_p($data);
$data_p = $ob->GetContent();
//$ob->Restore();
file_put_contents($load_debug_file, $data_p);

// save load debug info
$load_debug_file = $base_dir.$dir."/debug_load.html";
$ob->Clear();
include "tmpl/debug_head.php";
echo $result['load']['debug'];
$data_p = $ob->GetContent();
//$ob->Restore();
file_put_contents($load_debug_file, $data_p);

// save save debug info
$save_debug_file = $base_dir.$dir."/debug_save.html";
$ob->Clear();
include "tmpl/debug_head.php";
echo $result['save']['debug'];
$data_p = $ob->GetContent();
//$ob->Restore();
file_put_contents($save_debug_file, $data_p);

// save other debug info
$result_debug_file = $base_dir.$dir."/debug_res.html";
$ob->Clear();
unset($result['save']['debug']);
unset($result['load']['debug']);
unset($result['save']['data']);
unset($result['load']['data']);
include "tmpl/debug_head.php";
print_p($result);
$result_p = $ob->GetContent();
//$ob->Restore();
file_put_contents($result_debug_file, $result_p);

$ob->Clear();
echo $mainoutput;
?>
