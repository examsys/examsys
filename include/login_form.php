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
* @author Simon Atack, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

$configObject = Config::get_instance();
$cfg_root_path = $configObject->get('cfg_root_path');
?>
<!DOCTYPE html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $this->configObj->get('cfg_page_charset') ?>" />

  <title>Rog&#333; - <?php echo $string['signin'] ?></title>

  <link rel="stylesheet" type="text/css" href="<?php echo $cfg_root_path ?>/css/body.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $cfg_root_path ?>/css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $cfg_root_path ?>/css/login_form.css" />
  <?php
    if (isset($_SESSION['_lti_context'])) {
      echo "<style type=\"text/css\">\n  body {background-color:transparent !important}\n</style>\n"; // Make the LTI screen blend in more.
    }
  ?>
  <script type="text/javascript" src="<?php echo $cfg_root_path ?>/js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="<?php echo $cfg_root_path ?>/js/jquery.validate.min.js"></script>
	<script>
    $(function () {
      $('#username').focus();
			
<?php
  if (isset($displaystdformobj->scripts)) {
    foreach ($displaystdformobj->scripts as $script) {
		  echo $script;
		}
	}

?>
      if (!isCanvasSupported()){
        $('.html5warn').fadeIn();
      }

      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
		});
		
		function isCanvasSupported(){
			var elem = document.createElement('canvas');
			return !!(elem.getContext && elem.getContext('2d'));
		}
  </script>
</head>

<body>
<div class="html5warn"><?php echo $string['html5warn'] ?></div>
<form method="post" id="theform" autocomplete="off">
    <div class="mainbox">

        <img src="<?php echo $this->configObj->get('cfg_root_path') ?>/artwork/r_logo.gif" alt="logo" class="logo_img" />

        <div class="logo_lrg_txt">Rog&#333;</div>
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
                  <td><?php echo $string['username']; ?></td>
                  <td><input type="text" name="ROGO_USER" id="username" maxlength="60" value="<?php if (isset($_GET['guest_username'])) echo $_GET['guest_username']; ?>" class="field" autocomplete="off" <?php echo $required; ?> /></td>
              </tr>
              <tr>
                  <td><?php echo $string['password']; ?></td>
                  <td><input type="password" name="ROGO_PW" maxlength="60" value="<?php if (isset($_GET['guest_password'])) echo $_GET['guest_password']; ?>" class="field" autocomplete="off" <?php echo $required; ?> /></td>
              </tr>
<?php

            if (isset($displaystdformobj->fields)) {
							foreach($displaystdformobj->fields as $field) {
								if ($field->type == 'select') {
									echo '<tr>';
									echo '<td>' . $field->description . '</td>';
									echo '<td><select name="' . $field->name . '">';
									foreach($field->options as $name => $value) {
										$select='';
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
										$value='';
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
        
        <div class="versionno">Rog&#333; <?php echo $this->configObj->get('rogo_version') ?></div>

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
