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
 * @author Simon Atack, Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$configObject = Config::get_instance();
$cfg_root_path = $configObject->get('cfg_root_path');

$render = new render($configObject);
$headerdata = array(
    'css' => array(
        '/css/rogo_logo.css',
        '/css/login_form.css',
    ),
    'scripts' => array(
        '/js/logininit.min.js',
    ),
);

$js = '';
if (isset($displaystdformobj->scripts)) {
    foreach ($displaystdformobj->scripts as $script) {
        $js = '<script src="' . $cfg_root_path . '/plugins/auth/js/' . $script . '"/></script>';
    }
}

$css = '';
if (isset($_SESSION['_lti_context'])) {
    // Make the LTI screen blend in more.
    $css = "<style type=\"text/css\">\n  body {background-color:transparent !important}\n</style>\n";
}

$lang['title'] = 'ExamSys - ' . $string['signin'];
$render->render($headerdata, $lang, 'header.html', $js, $css);
?>
<div class="html5warn"><?php echo $string['html5warn'] ?></div>
<form method="post" id="theform" autocomplete="off">
    <div role="main" class="mainbox">

        <img src="<?php echo $this->configObj->get('cfg_root_path') ?>/artwork/r_logo.svg" alt="logo" class="logo_img" />

        <h1 class="logo_lrg_txt">ExamSys</h1>
        <div class="logo_small_txt"><?php echo $string['eassessmentmanagementsystem']; ?></div>

        <br/>
        <br/>
      <?php
        if (isset($displaystdformobj->messages)) {
            foreach ($displaystdformobj->messages as $object) {
                echo <<<HTML
$object->pretext
<div class="msg">$object->content</div>
$object->posttext
HTML;
            }
        }

        if (!(isset($displaystdformobj->replace) and $displaystdformobj->replace === true)) {
            echo "<div class=\"msg\">{$string['signinmsg']}</div>\n";
        }

        if (isset($displaystdformobj->disablerequired) and $displaystdformobj->disablerequired == true) {
            $required = '';
        } else {
            $required = 'required="required"';
        }

        ?>
        <div style="margin-left:65px">
          <table>
              <tr>
                  <td><label for="username"><?php echo $string['username']; ?></label></td>
                  <td><input type="text" name="ROGO_USER" id="username" maxlength="60" value="<?php if (isset($_GET['guest_username'])) {
                        echo $_GET['guest_username'];
                                                                                              } ?>" class="field" autocomplete="off" <?php echo $required; ?> /></td>
              </tr>
              <tr>
                  <td><label for="ROGO_PW"><?php echo $string['password']; ?></label></td>
                  <td><input type="password" name="ROGO_PW" id="ROGO_PW" maxlength="60" value="<?php if (isset($_GET['guest_password'])) {
                        echo $_GET['guest_password'];
                                                                                  } ?>" class="field" autocomplete="off" <?php echo $required; ?> /></td>
              </tr>
<?php

if (isset($displaystdformobj->fields)) {
    foreach ($displaystdformobj->fields as $field) {
        if ($field->type == 'select') {
            echo '<tr>';
            echo '<td>' . $field->description . '</td>';
            echo '<td><select name="' . $field->name . '">';
            foreach ($field->options as $name => $value) {
                $select = '';
                if ($value == $field->default) {
                    $select = 'selected';
                }
                            echo "<option value=\"$value\" $select>$name</option>\n";
            }
                        echo '</select></td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td>' . $field->description . '</td>';
            if (isset($_POST[$field->name])) {
                $value = $_POST[$field->name];
            } elseif (isset($field->defaultvalue) and $field->defaultvalue != '') {
                $value = $field->defaultvalue;
            } else {
                $value = '';
            }
            echo '<td><input type="' . $field->type . '" name="' . $field->name . '" value="' . $value . '" style="width:240px"></td>';
            echo '</tr>';
        }
    }
}
?>
          </table>
          <br/>
          </div>
          <div style="text-align:center"><input type="submit" name="rogo-login-form-std" value="<?php echo $string['signin']; ?>" class="ok" />
        <?php
        if (isset($displaystdformobj->buttons)) {
            foreach ($displaystdformobj->buttons as $object) {
                echo <<<HTML
$object->pretext
<input type="$object->type" name="$object->name" value="$object->value" style="$object->style" class="$object->class" />
$object->posttext
HTML;
            }
        }
        ?>
          </div>

      <?php
        if (isset($displaystdformobj->postbuttonmessages)) {
            foreach ($displaystdformobj->postbuttonmessages as $object) {
                $cssclass = 'msg';
                if (isset($object->cssclass)) {
                    $cssclass = $object->cssclass;
                }
                echo <<<HTML
$object->pretext
<div class="$cssclass">$object->content</div>
$object->posttext
HTML;
            }
        }
        ?>

        <div class="versionno">ExamSys <?php echo $this->configObj->get_setting('core', 'rogo_version') ?></div>

    </div>
</form>

<?php
if (isset($displaystdformobj->postformmessages)) {
    $cssareaclass = 'mainbox';
    if (isset($displaystdformobj->postformmessages[0]->cssareaclass)) {
        $cssclass = $object->cssclass;
    }
    if (!isset($displaystdformobj->postformmessages[0]->rawhtml)) {
        echo <<<HTML
<div class="$cssmainclass">
HTML;
        foreach ($displaystdformobj->postformmessages as $object) {
            $cssclass = 'msg';
            if (isset($object->cssclass)) {
                $cssclass = $object->cssclass;
            }
            echo <<<HTML
$object->pretext
<div class="$cssclass">$object->content</div>
$object->posttext
HTML;
        }
        echo <<<HTML
</div>
HTML;
    } else {
        echo $displaystdformobj->postformmessages[0]->rawhtml;
    }
}
?>
</body>
</html>
