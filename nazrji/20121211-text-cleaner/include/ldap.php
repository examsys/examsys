<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 01/08/12
 * Time: 13:36
 * To change this template use File | Settings | File Templates.
 */


function ldap_lookup($u, $p, &$data, $lookup_info=0) {
  $lookedup=ldap_auth($u, $p, $data, $lookup_info);
  return $lookedup;
}

function ldap_auth($u, $p, &$data, $lookup_info=0) {
  global $string;

  $configObj = Config::Instance();

  if ($u == '') {
    $data['error'] = $string['noldapusernamesupplied'];
    return false;
  }
  if ($p == '') {
    $data['error'] = $string['noldapusernamesupplied'];
    return false;
  }

  $ldap = ldap_connect($configObj->get('cfg_ldap_server'));
  ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
  if (ldap_bind($ldap, $configObj->get('cfg_ldap_bind_rdn'), $configObj->get('cfg_ldap_bind_password'))) {
    if (!($search = @ldap_search($ldap, $configObj->get('cfg_ldap_search_dn'), $configObj->get('cfg_ldap_user_prefix') . $u))) {
      $data['error'] = $string['ldapservernosearch'];
      return false;
    } else {
      $info = ldap_get_entries($ldap, $search);
      if($lookup_info === 1 and $info['count'] > 0) {
        return $info;
      }
      if ($info['count'] == 1) {
        $dn = $info[0]['dn'];
      } else {
        $data['error'] = '<strong>' . $string['noldapaccount'] . '</strong>';
        return false;
      }
    }

    if (@ldap_bind($ldap, $dn, utf8_encode($p))) {
      ldap_unbind($ldap);
      if($lookup_info === 2) {
        return $info;
      }
      return true;
    } else {
      $data['error'] = $string['incorrectpassword'];
      return false;
    }
  } else {
    $data['error'] = $string['ldapserverunavailable'];
    return false;
  }
}