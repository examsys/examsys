<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nazrji
 * Date: 04/07/12
 * Time: 10:30
 * To change this template use File | Settings | File Templates.
 */


require_once $cfg_web_root . 'classes/exceptions.inc.php';

class VLEFactory {
  public static function GetVLEAPI($vleapi) {
    $classname = 'VLE_' . $vleapi;
    $classfile = 'VLE_' . $vleapi . '.class.php';

    try {
      include $classfile;
      $object = new $classname();
    } catch (Exception $ex) {
      throw new ClassNotFoundException(sprintf($lang_strings['noclasserror'], $classname));
    }

    return $object;
  }
}
