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
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />

  <title>Undergoing Maintenance</title>

  <style type="text/css">
    body {font-family:Arial,sans-serif; font-size:100%; margin-top:20px; text-align:center; background-color:#EAEAEA}
    h1 {color: #C00000; font-size:180%; font-weight:bold}
    .box {width:60%; height:128px; margin-left:auto; margin-right:auto; border:2px solid #C00000; background-color:white}
  </style>
</head>

<body>
    <div class="box">
    <img src="/artwork/lrg_maintenance.png" width="128" height="128" alt="Under maintenance" style="float:left" />
    <h1>Undergoing Maintenance</h1>
    <div>ExamSys is currently undergoing routine maintenance. Please try again later.</div>
  </div>
</body>
</html>