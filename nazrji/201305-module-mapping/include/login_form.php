<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

$root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
$cfg_root_path = rtrim('/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $root), '/'), '/');
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $this->configObj->get('cfg_page_charset') ?>" />

  <title>Rog&#333; - <?php echo $string['signin']; ?></title>

  <link rel="stylesheet" type="text/css" href="<?php echo $cfg_root_path ?>/css/body.css"/>
  <link rel="stylesheet" type="text/css" href="<?php echo $cfg_root_path ?>/css/login_form.css"/>
</head>

<body>
<form method="post">
    <div class="mainbox">

        <img src="<?php echo $cfg_root_path ?>/artwork/r_logo.gif" width="56" height="60" alt="logo" style="float:left; padding-right:8px" />

        <div style="color:#1F497D;font-size:28pt; font-weight:bold">Rog&#333;</div>
        <div style="color:#1F497D;font-size:9pt">e-Assessment Management System</div>

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
      ?>
        <div style="margin-left:65px">
          <table>
              <tr>
                  <td><?php echo $string['username']; ?></td>
                  <td><input type="text" name="ROGO_USER" value="<?php if (isset($_GET['guest_username'])) echo $_GET['guest_username']; ?>" class="field" /></td>
              </tr>
              <tr>
                  <td><?php echo $string['password']; ?></td>
                  <td><input type="password" name="ROGO_PW" value="<?php if (isset($_GET['guest_password'])) echo $_GET['guest_password']; ?>" class="field" /></td>
              </tr>
<?php

            if (isset($displaystdformobj->fields)) {
              foreach($displaystdformobj->fields as $field) {
                echo '<tr>';
                echo '<td>' . $field->description . '</td>';
                if(isset($_POST[$field->name])) {
                  $value=$_POST[$field->name];
                } elseif(isset($field->defaultvalue) and $field->defaultvalue!='') {
                  $value=$field->defaultvalue;
                } else {
                  $value='';
                }
                echo '<td><input type="' . $field->type . '" name="' .$field->name . '" value="' . $value .'"></td>';
                echo '</tr>';
              }
            }
?>
          </table>
          <br/>
          </div>
          <div style="text-align:center"><input type="submit" name="rogo-login-form-std" value="<?php echo $string['signin']; ?>" style="width:150px" />
        <?php
        if (isset($displaystdformobj->buttons)) {
          foreach ($displaystdformobj->buttons as $object) {
            echo <<<HTML
$object->pretext
<input type="$object->type" name="$object->name" value="$object->value" style="$object->style" />
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