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
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

class IE_qti_Load extends IE_Main {
  var $params;

  function Load($params) {
    echo "<h4>Params</h4>";
    print_p($params);

    global $import_directory;

    $xml_files = array();

    //print_p($params);
    $this->params = $params;
    $import_directory = $params->base_dir.$params->dir."/";

    $filename = $params->sourcefile;

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if ($ext == "xml") {
      $xml_files[basename($filename)] = $filename;
    } else if ($ext == "zip") {
      echo "Extracting zip<br>";
      $zip = new ZipArchive;
      $res = $zip->open($filename);
      if ($res === TRUE) {
        $zip->extractTo($params->base_dir.$params->dir."/");
        for ($i = 0; $i < $zip->numFiles ; $i++) {
          $stat = $zip->statIndex($i);
          $filename = $stat['name'];
          $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
          if ($ext == "xml") {
            $xml_files[$filename] = $params->base_dir.$params->dir."/".$filename;
          }
        }
        $zip->close();
      } else {
        $this->AddError("Invalid Zip file Uploaded");
        return;
      }
    }

    //print_p($xml_files);

    $files['qti12'] = array(); // qti 1.2 files, each unrelated to the rest
    $files['manifest'] = array(); // manifest files
    $files['item'] = array(); // qti 2 questions
    $files['paper'] = array(); // qti 2 test files

    foreach ($xml_files as $filename => $fullpath) {
      $type = $this->DetectFileType($fullpath);
      $files[$type][$filename] = $fullpath;
    }

    //print_p($files);

    if (count($files['qti12']) == 0) {
      $this->AddError("No QTI XML files in the zip file");
      return;
    }

    $result = new stdClass();
    $result->questions = array();

    // process qti 1.2 files
    foreach ($files['qti12'] as $filename => $fullpath) {
      $qti12 = new IE_QTI12_Load();

      $params->sourcefile = $fullpath;
      $ob = new OB();
      $ob->ClearAndSave();
      $output = $qti12->Load($params);
      $this->debug .= $ob->GetContent();
      $ob->Restore();
      foreach ($qti12->warnings as $qid => $warnings) foreach ($warnings as $warn) $this->warnings[$qid][] = $warn;

      foreach ($qti12->errors as $qid => $errors) foreach ($errors as $error) $this->errors[$qid][] = $error;

      echo "<h4>File Output : $filename</h4>";
      echo $this->debug;

      foreach ($output->questions as $id => $question) $result->questions[$id] = $question;

      if (!empty($output->papers)) foreach ($output->papers as $id => $paper) $result->papers[$id] = $paper;
    }

    //print_p($result);

    return $result;
  }

  function DetectFileType($filename) {
    $xmlStr = file_get_contents($filename);
    $xml = simplexml_load_string($xmlStr);

    if (!$xml) {
      $this->AddError(basename($filename)." is an invalid XML file");
      return '';
    }

    $basenode = strtolower($xml->getName());
    if ($basenode == "questestinterop") return "qti12";

    if ($basenode == "assessmentitem") return "item";

    if ($basenode == "manifest") return "manifest";

    if ($basenode == "assessmenttest") return "paper";

    echo $basenode."<br>";
    //print_p($xml);
    }
}
