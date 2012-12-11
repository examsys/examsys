<?php

function do_admin_login($browser) {
  $browser->open("/staff/");
  $browser->type("name=PHP_USER", "selenium");
  $browser->type("name=PHP_PW", "srh*63Hh");
  $browser->click("name=submit-UserPW");
  $browser->waitForPageToLoad("30000");
}

function do_staff_login($browser) {
  $browser->open("/staff/");
  $browser->type("name=PHP_USER", "teststaff");
  $browser->type("name=PHP_PW", "lxn&98X21");
  $browser->click("name=submit-UserPW");
  $browser->waitForPageToLoad("30000");
}