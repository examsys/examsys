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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../../include/staff_auth.inc';

$maxscreen = param::required('max_screen', param::INT, param::FETCH_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['questionsbank']); ?></title>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src="../../js/require.js"></script>
  <script src="../../js/main.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/add_questions.css" />
</head>
<body>
<div>
    <div class="wrapper">
        <div id="qbuttons">
            <table cellspacing="0" cellpadding="0" style="font-size:90%; width:126px; height:99%; background-color:white; border:1px solid #828790">
            <tr><td style="vertical-align:top; text-align:center">

            <table cellspacing="0" cellpadding="0" style="font-size:90%; width:144px; text-align:left">
            <tr><td id="button_unused" class="tabon"><?php echo $string['myunused'] ?></td></tr>
            <tr><td id="button_alphabetic" class="tab"><?php echo $string['allmyquestions'] ?></td></tr>
            <tr><td id="button_keywords" class="tab"><?php echo $string['bykeywords'] ?></td></tr>
            <tr><td id="button_status" class="tab"><?php echo $string['bystatus'] ?></td></tr>
            <tr><td id="button_papers" class="tab"><?php echo $string['bypaper'] ?></td></tr>
            <?php
              $user_modules = $userObject->get_staff_modules();

            if (count($user_modules) > 0) {
                echo '<tr><td id="button_team" class="tab">' . $string['byteam'] . '</td></tr>';
            } else {
                echo '<tr><td id="button_team" class="tab grey">' . $string['byteam'] . '</td></tr>';
            }
            ?>
            <tr><td id="button_search" class="tab"><?php echo $string['search'] ?></td></tr>
            </table>

            </td></tr>
            </table>
        </div>
        <div id="qlist">
            <iframe id="iframeurl" src="add_questions_list.php?type=unused" name="iframeurl" frameborder="0">
                <p><?php echo $string['browsererr'];?></p>
            </iframe>
            <iframe id="previewurl" src ="preview_default.php" name="previewurl"  frameborder="0">
                <p><?php echo $string['browsererr'];?></p>
            </iframe>
        </div>
      <div name="controls" id="controls">
          <form id="addquestions" name="theform" method="post" action="" autocomplete="off">
            <div align="right"><label for="screen"><?php echo $string['screen'] ?></label>
                  <select name="screen">
                  <?php
                    for ($i = 1; $i <= $maxscreen + 1; $i++) {
                        if ($i == $maxscreen) {
                            $selected = 'selected="selected"';
                        } else {
                            $selected = '';
                        }
                        echo "<option value=\"$i\" $selected>$i</option>\n";
                    }
                    ?>
                  </select>
                  <input type="hidden" name="questions_to_add" id="questions_to_add" value="" />
                  <input type="submit" name="submit" value="<?php echo $string['addquestions'] ?>" />
              </div>
          </form>
      </div>
    </div>
</div>
<?php
// Dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['paperid'] = param::required('paperID', param::INT, param::FETCH_GET);
$miscdataset['attributes']['module'] = param::optional('module', '', param::INT, param::FETCH_GET);
$miscdataset['attributes']['folder'] = param::optional('folder', '', param::INT, param::FETCH_GET);
$miscdataset['attributes']['disp'] = param::required('display_pos', param::INT, param::FETCH_GET);
$miscdataset['attributes']['srcofy'] = param::required('scrOfY', param::FLOAT, param::FETCH_GET);
$miscdataset['attributes']['max'] = $maxscreen;
$render->render($miscdataset, array(), 'dataset.html');
?>
<script src="../../js/questionsframeinit.min.js"></script>
</body>
</html>
