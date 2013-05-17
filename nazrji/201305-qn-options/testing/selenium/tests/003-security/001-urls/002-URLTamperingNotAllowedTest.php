<?php
require_once 'shared.inc.php';

class URLTamperingNotAllowedTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  // public function testAddToTeamNotAllowed() {
  //   do_staff_login($this);

  //   $this->open("/folder/edit_team_popup.php?module=888207&calling=paper_list&folder=");
  //   $this->assertTextPresent('Page not Found');
  // }
}
?>