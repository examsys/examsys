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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

// parameters

// dest - qti12 or qti21
// type - paper or question
// ids - comma separated list of question or paper ids (supports multiple papers as multiple qti files)

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once 'include/inc.php';
require_once 'local/local_load.php';
require_once 'qti12/qti12_save.php';
require_once 'qti20/qti20_save.php';

$ids = check_var('paperID', 'GET', true, false, true);
$dest = check_var('dest', 'GET', true, false, true);

// Get question statuses
$status_tmp = QuestionStatus::get_all_statuses($mysqli, $string, true);
$statuses = array();
foreach ($status_tmp as $sid => $status) {
  $statuses[$sid] = $status->get_name();
}

$ob = new OB();

if ($dest == 'qti21') $dest = 'qti20';

if (isset($_GET['debug'])) {
  $show_debug = true;
} else {
  $show_debug = false;
}

// load in some paper information to display
if ($ids != '') {
  $type = 'paper';

  // get paper properties
  $properties = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli, $string);
  $paper_title = $properties->get_paper_title();
} else {
  $ids = GetVar("q_id");
  $type = 'question';

  $paper_title = 'Questions';
}

// set up classes
$load_params = new stdClass();
$load_params->source = 'rogo';
$load_params->type = $type;
$load_params->ids = explode(",", $ids);
$paperID = $load_params->ids[0];
$import = new IE_Local_Load();
$import->setStatuses($statuses);

if ($dest == "qti12") {
  $export = new IE_QTI12_Save();
} else if ($dest == "qti20") {
  $export = new IE_QTI20_Save();
} else {
  die("Invalid destination - $dest");
}

// create dir for qti to save into, and put in params
$qtiexportdirectory = rogo_directory::get_directory('qti_export');
$base_dir = $qtiexportdirectory->location();
$dir = GetAuthorName($userObject->get_user_ID())."/".date("Y-m-d")."/".date("H.i.s"); //TODO replace with userobject function
if (!file_exists($base_dir.$dir)) mkdir($base_dir.$dir, 0755, true);
$save_params = new stdClass();
$save_params->dir = $dir;
$save_params->base_dir = $base_dir;

$load_params->dir = $dir;
$load_params->base_dir = $base_dir;

///////////////////////
// perform operation //
///////////////////////

$result = array();
$general_params = new stdClass();
$result['general']['params'] = $general_params;

// call import with imptype and params
$ob->ClearAndSave();
$data = $import->Load($load_params);
$result['load']['type'] = 'rogo';
$result['load']['params'] = $load_params;
$result['load']['debug'] = $ob->GetContent();
$result['load']['warnings'] = $import->warnings;
$result['load']['errors'] = $import->errors;
$result['load']['data'] = $data;
$ob->Restore();

// create object with save source
$ob->ClearAndSave();
$export->Save($save_params, $data);
$result['save']['type'] = $dest;
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
require "tmpl/export_main.php";
$mainoutput = $ob->GetContent();

// store page with details of all questions imported or exported
$ob->Clear();
require "tmpl/export_details.php";
$result_debug_file = $base_dir.$dir."/result.html";
$output = $ob->GetContent();
file_put_contents($result_debug_file, $output);

// save access information and other stuff into the destination folder
$access_file = $base_dir.$dir."/access.xml";
$ob->Clear();
require "tmpl/access.php";
$access = $ob->GetContent();
file_put_contents($access_file, $access);

// store intermediate format debug information
$load_debug_file = $base_dir.$dir."/debug_int.html";
$ob->Clear();
require "tmpl/debug_head.php";
print_p($data);
$data_p = $ob->GetContent();
file_put_contents($load_debug_file, $data_p);

// store intermediate format debug information -  plain version
$load_debug_file = $base_dir.$dir."/debug_int.txt";
$ob->Clear();
require "tmpl/debug_head.php";
print_r($data);
$data_p = $ob->GetContent();
file_put_contents($load_debug_file, $data_p);

// save load debug info
$load_debug_file = $base_dir.$dir."/debug_load.html";
$ob->Clear();
include "tmpl/debug_head.php";
echo $result['load']['debug'];
$data_p = $ob->GetContent();
file_put_contents($load_debug_file, $data_p);

// save save debug info
$save_debug_file = $base_dir.$dir."/debug_save.html";
$ob->Clear();
include "tmpl/debug_head.php";
echo $result['save']['debug'];
$data_p = $ob->GetContent();
file_put_contents($save_debug_file, $data_p);

// save other debug info
$result_debug_file = $base_dir.$dir."/debug_res.html";
$ob->Clear();
unset($result['save']['debug']);
unset($result['load']['debug']);
unset($result['save']['data']);
unset($result['load']['data']);
require "tmpl/debug_head.php";
print_p($result);
$result_p = $ob->GetContent();
file_put_contents($result_debug_file, $result_p);

// save other debug info -  plain version
$result_debug_file = $base_dir.$dir."/debug_res.txt";
$ob->Clear();
print_r($result);
$result_p = $ob->GetContent();
file_put_contents($result_debug_file, $result_p);

$ob->Clear();
echo $mainoutput;

?>