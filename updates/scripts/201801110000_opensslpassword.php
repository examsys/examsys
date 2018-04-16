<?php
/**
 * Decrypt the password used here for replacing itself and mcrypt with openssl
 *
 * @param string $encpassword encrypted password
 * @return string decrypted password
 */
function rogo2272_mdecrypt_password($encpassword) {
  $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
  $dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, UserUtils::get_salt(), base64_decode($encpassword), MCRYPT_MODE_ECB, $iv);
  return trim($dec);
}

if ($updater_utils->check_version("6.5.0")) {
  if (!$updater_utils->has_updated('rogo2272')) {
    // Update paper passwords.
    $result = $mysqli->prepare("SELECT property_id, password from properties WHERE password is not null and password != ''");
    $result->execute();
    $result->store_result();
    $result->bind_result($property_id, $password);
    $passwords = array();
    while ($result->fetch()) {
      $oldpass = rogo2272_mdecrypt_password($password);
      $passwords[$property_id] = \encryp::openssl_encrypt_decrypt("encrypt", $oldpass) ;
    }

    $update = $mysqli->prepare("UPDATE properties SET password = ? WHERE property_id = ?");
    foreach ($passwords as $p_id => $pass) {
      $update->bind_param('si', $pass, $p_id);
      $update->execute();
    }

    $result->close();
    $update->close();

    // Update plugin passwords
    $result2 = $mysqli->prepare("SELECT component, setting, value from config WHERE type = 'password' and value != ''");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($component, $setting, $value);
    $passwords = array();
    while ($result2->fetch()) {
      $oldpass = rogo2272_mdecrypt_password($value);
      $passwords[$component] = array($setting, \encryp::openssl_encrypt_decrypt("encrypt", $oldpass));
    }

    $update2 = $mysqli->prepare("UPDATE config SET value = ? WHERE setting = ? and component = ? and type = 'password'");
    foreach ($passwords as $component => $setting) {
      $value = $setting[1];
      $name = $setting[0];
      $update2->bind_param('sss', $value, $name, $component);
      $update2->execute();
    }

    $result2->close();
    $update2->close();

    $updater_utils->record_update('rogo2272');
  }
}