<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 26/07/12
 * Time: 09:49
 * To change this template use File | Settings | File Templates.
 */

require_once 'ims-lti\UoN_LTI.php';
$root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
$root = $root . '/../';

require_once  $root . 'include/load_config.php';
require_once $cfg_web_root . 'classes/dbutils.class.php';

//print_r($_REQUEST);
$mysqli = DBUtils::get_mysqli_link($cfg_db_host, $cfg_db_username, $cfg_db_passwd, $cfg_db_database, $cfg_db_charset, $notice, $dbclass);


$lti = new UoN_LTI($mysqli);
$lti->init_lti0($mysqli);
$lti->init_lti(true, false);

print "<pre>";
print_r($lti);


var_dump($lti);


echo $lti->dump();

print "</pre>";