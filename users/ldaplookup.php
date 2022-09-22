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
 * Creates a new user (staff or student).
 *
 * @author Simon Wilkinson, Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/admin_auth.inc';

?>
<!DOCTYPE html>
<html>
<head>
   <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>LDAP <?php echo $string['lookup'] ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/list.css"/>
    <style type="text/css">
        body {font-size:90%}
    th {background-color:#295AAD; color:white; text-align:left; font-weight:normal}
   </style>
    <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src="../js/ldaplookupinit.min.js"></script>
</head>
<body>
<?php
if (isset($_POST['submit'])) {
    $lookup = Lookup::get_instance($configObject, $mysqli);
    $data = new stdClass();
    $data->lookupdata = new stdClass();
    if ($_REQUEST['username'] != '') {
        $data->lookupdata->username = $_REQUEST['username'];
        $data->searchorder = array('username');
    }
    if ($_REQUEST['surname'] != '') {
        $data->lookupdata->surname = $_REQUEST['surname'];
        $data->searchorder = array('surname');
    }

    $data->settings = new stdClass();
    $output = $lookup->userlookup($data);
    ini_set('xdebug.remote_autostart', 1);
    ini_set('display_errors', 1);
    ini_set('xdebug.var_display_max_childrren', -1);
    ini_set('xdebug.var_display_max_data', -1);
    ini_set('xdebug.var_display_max_depth', -1);

    if (isset($output->success)) {
        if (!isset($output->lookupdatas)) {
            ?>
    <form method="post" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
            <div style="text-align:center">
                <table style="text-align:left">
                    <?php
                    if (isset($_POST['username']) and $_POST['username'] != '') {
                        echo '<tr><td>' . $string['username'] . '</td><td><input type="text" name="username" value="' . $_POST['username'] . '" size="20" style="border: 1px solid #800000; background-color:#FFC0C0" /><span>' . $string['nousersalert'] . "</span></td></tr>\n";
                    } else {
                        echo '<tr><td>' . $string['username'] . "</td><td><input type=\"text\" name=\"username\" value=\"\" size=\"20\" /></td></tr>\n";
                    }
                    if (isset($_POST['surname']) and $_POST['surname'] != '') {
                        echo '<tr><td>' . $string['surname'] . '</td><td><input type="text" name="surname" value="' . $_POST['surname'] . '" size="40" style="border: 1px solid #800000; background-color:#FFC0C0" /><span>' . $string['nousersalert'] . "</span></td></tr>\n";
                    } else {
                        echo '<tr><td>' . $string['surname'] . "</td><td><input type=\"text\" name=\"surname\" value=\"\" size=\"40\" /></td></tr>\n";
                    }
                    ?>
                    <tr>
                        <td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['lookup'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" /></td>
                   </tr>
              </table>
           </div>
    </form>
    </body>
</html>
            <?php
            exit();
        } else {
            $user_data = array();
            $user = 0;
            echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"width:100%; background-color:white\">\n";
            echo '<tr style="cursor:pointer"><th>' . $string['title'] . '</th><th>' . $string['first_names'] . '</th><th>' . $string['surname'] . '</th><th>' . $string['username'] . '</th><th>' . $string['email'] . '</th><th>' . $string['role'] . "</th></tr>\n";
            foreach ($output->lookupdatas as $key => $object) {
                if (isset($object->title)) {
                    $user_data[$user]['title'] = $object->title;
                } else {
                    $user_data[$user]['title'] = '';
                }
                if (isset($object->firstname)) {
                    $user_data[$user]['first_names'] = $object->firstname;
                } else {
                    $user_data[$user]['first_names'] = '';
                }
                if (isset($object->surname)) {
                    $user_data[$user]['surname'] = $object->surname;
                } else {
                    $user_data[$user]['surname'] = '';
                }
                if (isset($object->username)) {
                    $user_data[$user]['username'] = $object->username;
                } else {
                    $user_data[$user]['username'] = '';
                }
                if (isset($object->email)) {
                    $user_data[$user]['email'] = $object->email;
                } else {
                    $user_data[$user]['email'] = '';
                }
                if (isset($object->role)) {
                    $user_data[$user]['role'] = $object->role;
                } else {
                    $user_data[$user]['role'] = '';
                }
                if (isset($object->school)) {
                    $user_data[$user]['school'] = $object->school;
                } else {
                    $user_data[$user]['school'] = '';
                }
                $user_data[$user]['key'] = $key;
                $user_data[$user]['object'] = $object;
                $user++;
            }
        }

        if ($user > 1) {
            $user_data = \sort::array_csort($user_data, 'first_names', 'asc');
        }
        unset($_SESSION['ldaplookupdata']);

        for (
            $i = 0; $i < $user; $i++
        ) {
            $title                = $user_data[$i]['title'];
            $first_names  = $user_data[$i]['first_names'];
            $surname          = $user_data[$i]['surname'];
            $username         = $user_data[$i]['username'];
            $email                = $user_data[$i]['email'];
            $school               = $user_data[$i]['school'];
            $role                 = $user_data[$i]['role'];
            $key                  = $user_data[$i]['key'];
            $object               = $user_data[$i]['object'];
            $_SESSION['ldaplookup'][$i] = $key;
            $_SESSION['ldaplookupdata'][$key] = $object;
            echo "<tr class=\"l\" id=\"$key\"><td>$title</td><td>$first_names</td><td>$surname</td><td>$username</td><td>$email</td><td>$role</td></tr>\n";
        }
        echo "</table>\n";
    }

    $render = new render($configObject);
    $jsdataset['name'] = 'jsutils';
    $jsdataset['attributes']['xls'] = json_encode($string);
    $render->render($jsdataset, array(), 'dataset.html');
    echo "</body>\n</html>\n";
    exit();
}

?>
<body>

<form method="post" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
  <div style="text-align:center">
        <table style="text-align:left">
            <tr>
                <td><?php echo $string['username'] ?></td>
                <td><input type="text" name="username" size="20"/></td>
            </tr>
          <tr>
                <td><?php echo $string['surname'] ?></td>
             <td><input type="text" name="surname" size="40"/></td>
         </tr>
          <tr>
                <td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['lookup'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" />
                </td>
          </tr>
      </table>
   </div>
</form>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
