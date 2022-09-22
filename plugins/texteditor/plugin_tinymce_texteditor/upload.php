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

// Upload file from tinymce script. Based on example at https://www.tiny.cloud/docs/advanced/php-upload-handler/

require '../../../include/staff_auth.inc';

// Don't attempt to process the upload on an OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    return;
}

// Parameter to distinguish how filenames are treated.
// Help files keep the name supplied. Questions generated a unique name.
$uploadtype = param::optional('type', null, param::ALPHA, param::FETCH_GET);

reset($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])) {
    // Sanitize input
    if (preg_match('/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/', $temp['name'])) {
        header('HTTP/1.1 400 Invalid file name.');
        return;
    }

    // Verify extension
    $supportedimages = array();
    foreach (\media_handler::SUPPORTED as $ext => $type) {
        if ($type === \questiondata::IMAGE) {
            $supportedimages[] = $ext;
        }
    }
    if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), $supportedimages)) {
        header('HTTP/1.1 400 Invalid extension.');
        return;
    }

    // Determine the base URL
    $headers = getallheaders();
    if (mb_strpos($headers['Referer'], '/help/staff/') !== false) {
        $mediatype = 'help_staff';
    } elseif (mb_strpos($headers['Referer'], '/help/student/') !== false) {
        $mediatype = 'help_student';
    } else {
        $mediatype = 'media';
    }

    // Accept upload
    $mediadirectory = rogo_directory::get_directory($mediatype);
    $filename = mb_strtolower($temp['name']);
    if ($uploadtype === 'help') {
        // Help files keep the image name.
        $unique_name = $filename;
    } else {
        $unique_name = \media_handler::unique_filename($filename);
    }
    $fullpath = $mediadirectory->fullpath($unique_name);
    move_uploaded_file($temp['tmp_name'], $fullpath);

    $fullurl = '/getfile.php?type=' . $mediatype . '&filename=' . $unique_name;

    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    // { location : '/your/uploaded/image/file'}
    echo json_encode(array('location' => $fullurl));
} else {
    // Notify editor that the upload failed
    header('HTTP/1.1 500 Server Error');
}
