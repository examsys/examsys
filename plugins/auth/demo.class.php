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
 * Handles demo functionality at login.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */


$cfg_web_root = $configObject->get('cfg_web_root');
require_once 'outline_authentication.class.php';
require_once 'internaldb.class.php';

class demo_auth extends internaldb_auth {

  public $impliments_api_auth_version = 1;
  public $version = 1.0;

  private $unique_username;
  private $unique_coursename;

  private $errmess='';

 function init($object) {
    parent::init($object);

    if (!isset($this->settings['school']) or $this->settings['school']='') {
      $this->set_error('Couldnt bind to ldap server');
      return $object;
    }
    if (!is_int($this->settings['school'])) {
      $this->settings['school']=SchoolUtils::get_school_id_by_name($this->settings['school'], $this->db);
    }

    return $object;
  }

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'createaccount'), 'preauth', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'demobutton'), 'displaystdform', $this->number, $this->name);

    return $callbackarray;
  }

  function createaccount($preauthobj) {
    //only run if
    if (!(isset($this->form['std']->username) or isset($this->form['std']->username)) and isset($this->request['createnewdemoaccount'])) {
      $this->savetodebug('Create Account button pressed');
    }

    $unique_username = true;
    $unique_module = true;

    $new_moduleid = '';

    for ($a = 0; $a < strlen($this->request['new_grade2']); $a++) {
      print "RT";
      $b = substr($this->request['new_grade2'], $a, 1);
      print $b;
      if (ctype_upper($b) or ctype_digit($b)) {
        print "i";
        $new_moduleid = $new_moduleid . $b;
      }
    }
    if ($new_moduleid == '') {
      $new_moduleid=$this->request['new_grade2'];
    }

    module_utils::add_modules(
        $new_moduleid,
        $_POST['new_grade2'],
        1,
        $this->settings['school'],
        '',
        '',
        0,
        false,
        false,
        false,
        true,
        0,
        null,
        $this->db,
        0,
        0,
        1,
        1,
        '07/01'
    );

    return $preauthobj;

  }

  function auth($authobj) {
    if (isset($this->form['std']->username) or isset($this->form['std']->username)) {
      //return not sucessfull do not try
      $this->savetodebug('Username/Password entered');

      $authobj->fail($this->number);
      $authobj->message = 'username or password entered for normal login';

      return $authobj;
    }

    $this->savetodebug('Not run');
    $authobj->fail($this->number);

    return $authobj;
  }


  function my_ucwords($s) {
    $s = preg_replace_callback("/(?:^|-|\pZ|')([\pL]+)/su", '$this->fixcase_callback', $s);
    return $s;
  }

  function fixcase_callback($word) {
    $word = $word[1];
    $word = mb_strtolower($word, 'UTF-8');

    if ($word == "de") return $word;

    $word = mb_ucasefirst($word);

    if (mb_substr($word, 1, 1, 'UTF-8') == "'") {
      if (mb_substr($word, 0, 1, 'UTF-8') == "D") {
        $word = mb_strtolower($word, 'UTF-8');
      }
      $next = mb_substr($word, 2, 1, 'UTF-8');
      $next = mb_strtoupper($next, 'UTF-8');
      $word = mb_substr_replace($word, $next, 2, 1, 'UTF-8');
    }
    return $word;
  }

  function demobutton($displaystdformobj) {
    global $string, $language;

    $this->savetodebug('Demo Info');

    $this->savetodebug('Adding New Demo Button');
    $postbuttonmessage = new displaystdformmessage();
    $postbuttonmessage->pretext = <<<HTML
<script>

    $(function () {

        $(".slidingDiv").hide();
        $(".show_hide").show();

    $('.show_hide').click(function(){
    $(".slidingDiv").slideToggle();
    });

});

</script>
HTML;
        $postbuttonmessage->pretext = $postbuttonmessage->pretext . '<br><a href="#" class="show_hide">Create Demo Account</a><br/>';
            $content0 = <<<HTML

    <script>
        function checkForm() {
            if (document.newUser.new_first_names.value == "") {
                alert("$string[reqfirstname]");
                return false;
            }
            if (document.newUser.new_surname.value == "") {
                alert("$string[reqsurname]");
                return false;
            }
            if (document.newUser.new_email.value == "" || document.newUser.new_email.value == "@example.com") {
                alert("$string[reqemail]");
                return false;
            }
            if (document.newUser.new_grade.options[document.newUser.new_grade.selectedIndex].value == "") {
                alert("$string[reqcourse]");
                return false;
            }
            if (document.newUser.new_username.value == "") {
                alert("$string[requsername]");
                return false;
            } else {
                username = document.newUser.new_username.value;
                for (a = 0; a < username.length; a++) {
                    char = username.substr(a, 1);
                    if (char == '_') {
                        alert('$string[usernamechars]');
                        return false;
                    }
                }
            }
            if (document.newUser.new_password.value == "") {
                alert("$string[reqpassword]");
                return false;
            }
        }

    </script>
HTML;

        $stfsel = '';
        if (isset($_POST['new_type']) and $_POST['new_type'] == 'Staff') $stfsel = ' checked';
        $stusel = '';
        if (isset($_POST['new_type']) and $_POST['new_type'] == 'Student') $stusel = ' checked';
if($stfsel === '' and $stusel === '') $stfsel = ' checked';

        $content1 =<<<HTML
<div id="content">
<br/>
    <form method="post" name="newUser" onsubmit="return checkForm()" action="$_SERVER[PHP_SELF]" autocomplete="off">
        <div align="center">
            <table border="0" cellspacing="1" cellpadding="0" style="background-color:#95AEC8; text-align:left">
                <tr>
                    <td>
                        <table border="0" cellspacing="6" cellpadding="0" width="100%" style="background-color:white">
                            <tr>
                                <td width="32"><img src="../artwork/user_female_32.png" width="32" height="32"
                                                    alt="User Icon"/></td>
                                        <td><table><tr>
                                                                        <td class="title">$string[register1]</td><td>
<input type="radio" name="new_type" value="Staff" $stfsel>Staff User<br>
<input type="radio" name="new_type" value="Student" $stusel>Student User
                                </td>
                                        </tr></table></td>

                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table border="0" cellspacing="6" cellpadding="0" style="background-color:#F1F5FB">
                            <tr>
                                <td colspan="2" class="h">Your Details</td>
                            </tr>
                            <tr>
                                <td align="right"><span class="field">$string[title]</span></td>
                                <td>
                                    <select id="new_users_title" name="new_users_title" size="1">
HTML;

        $content2 = '';
        if ($language != 'en') {
          $content2 = "<option value=\"\"></option>\n";
        }
        $titles = explode(',', $string['title_types']);
        foreach ($titles as $tmp_title) {
          $content2 .= "<option value=\"$tmp_title\">$tmp_title</option>";
        }

        $first_names = '';
        if (isset($_POST['new_first_names'])) $first_names = $_POST['new_first_names'];
        $surname = '';

        if (isset($_POST['new_surname'])) $surname = $_POST['new_surname'];

        $email = '';
        if (isset($_POST['new_email'])) $email = $_POST['new_email'];
        $usrnmstyle='';
        if(!isset($_POST['new_username'])) $_POST['new_username']='';
        if (isset($_POST['new_username']) and $this->unique_username != true) $usrnmstyle = ' style="background-color:#FFD9D9; color:#800000; border:1px solid #800000" value="' . $_POST['new_username'] . '"';


        if (isset($_POST['new_password']) and $_POST['new_password']!='') {
          $newpass = $_POST['new_password'];
        } else {
          $enc = new encryp();
          $generated_password = $enc->gen_password(true);
          $newpass = $generated_password['password'];
        }
        $msel = '';
        if (isset($_POST['new_gender']) and $_POST['new_gender'] == 'Male') $msel = ' selected';
        $fsel = '';
        if (isset($_POST['new_gender']) and $_POST['new_gender'] == 'Female') $fsel = ' selected';

        $newgrade2 = '';
        if (isset($_POST['new_grade2']) and $this->unique_coursename != true) {
          $newgradestyle = ' style="background-color:#FFD9D9; color:#800000; border:1px solid #800000" value="' . $_POST['new_username'] . '"';
        } elseif( isset($_POST['new_grade2'])) {
          $newgradestyle = 'value="' . $_POST['new_grade2'] . '"';
        }

        $content3 = <<<HTML
                                    </select></td>
                            </tr>
                            <tr>
                                <td align="right"><span class="field">$string[firstnames]</span></td>
                                <td><input type="text" id="new_first_names" name="new_first_names" size="40"
                                           value="$first_names"/>
                                </td>
                            </tr>
                            <tr>
                                <td align="right"><span class="field">$string[lastname]</span></td>
                                <td><input type="text" id="new_surname" name="new_surname" size="40"
                                           value="$surname"/></td>
                            </tr>
                            <tr>
                                <td align="right"><span class="field">$string[email]</span></td>
                                <td><input type="text" id="new_email" name="new_email" size="40"
                                           value="$email"/></td>
                            </tr>
                            <tr>
                                <td align="right"><span class="field">$string[username]</span></td>
                                <td><input type="text" id="new_username" name="new_username"
                                           size="12" $usrnmstyle/>
                                    </td></tr><tr><td align="right"><span class="field">$string[password]</span></td><td>
                                    <input type="text" id="new_password" name="new_password" value="$newpass" size="12"/></td>
                            </tr>

                            <input type="hidden" name="new_year" value="1"/>

                            <tr>
                                <td align="right"><span class="field">$string[gender]</span></td>
                                <td>
                                    <select id="new_gender" name="new_gender" size="1">
                                        <option value=""></option>
                                        <option value="Male" $msel>$string[male]</option>
                                        <option value="Female" $fsel>$string[female]</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2" class="h">$string[demomodule]</td>
                            </tr>

                            <tr>
                                <td align="right"><span class="field">$string[name]</span></td>
                                <td>
                                    <input type="text" id="new_grade2" name="new_grade2" size="40"
                                           $newgradestyle />
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><input type="hidden" name="new_welcome" value="1"/>&nbsp;$this->errmess</td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" name="createnewdemoaccount" value="$string[createaccount]"/>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <input type="hidden" size="15" name="new_sid"/>
        </div>
HTML;

    $content = $content0.$content1.$content2.$content3;



    $newbutton = new displaystdformobjbutton();
    $newbutton->type = 'button';
    $newbutton->value = ' Create Demo Account ';
    $newbutton->pretext= <<<HTML
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.js" type="text/javascript"></script>
<script>

    $(function () {

        $(".slidingDiv").hide();
        $(".show_hide").show();

    $('.show_hide').click(function(){
    $(".slidingDiv").slideToggle();
    });

});

</script>
<br>
HTML;
    $newbutton->name = 'showcreatedemoaccount';
    $newbutton->class = 'show_hide';
    $newbutton->posttext = '<div class="slidingDiv">' . $content . '</div>';
    $displaystdformobj->buttons[] = $newbutton;

    return $displaystdformobj;
  }

  function errordisp($displayerrformobj) {
    global $string;
    $cfg = Config::get_instance();
    
    $this->savetodebug('adding forgotten password link ');
    $displayerrformobj->li[] = '<a href="' . $cfg->get('cfg_root_path') . '/users/forgotten_password.php">' . $string['forgottenpassword'] . '</a>' ;

    return $displayerrformobj;
  }

}
