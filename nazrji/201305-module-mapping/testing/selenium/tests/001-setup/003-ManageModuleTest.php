<?php
require_once 'shared.inc.php';

class ManageModuleTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testCreateModule() {
    do_admin_login($this);

    $this->open("/admin/index.php");
    $this->click("css=#11 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Modules' . $this->install_type);

    $this->click("link=Create new Module");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Create new Module' . $this->install_type);

    $this->type("name=modulecode", "S02SHL");
    $this->type("name=fullname", "Short Lived");
    $this->select("name=schoolid", "label=School of Selenium Testing");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('S02SHL');
  }

  /**
   * @depends testCreateModule
   */
  public function testEditModule() {
    do_admin_login($this);

    $this->open("/admin/list_modules.php");
    $this->click("//tr[@id='4']/td[2]/div");
    $this->click("link=Edit Module");
    $this->waitForPageToLoad("30000");
    $this->type("name=fullname", "Short Lived2");
    $this->type("name=modulecode", "S02SHL2");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");

    $this->assertTextPresent('S02SHL2');
    $this->assertTextPresent('Short Lived2');
    // TODO: test change of faculty
  }

  public function testCantCreateModuleWithoutCode() {
    do_admin_login($this);

    $this->open("/admin/list_modules.php");
    $this->click("link=Create new Module");
    $this->waitForPageToLoad("30000");
    $this->type("name=fullname", "Should Not Exist");
    $this->select("name=schoolid", "label=School of Selenium Testing");
    $this->click("name=submit");

    $this->assertTextPresent('Please enter an Identifier for the module');
  }


  public function testCantCreateModuleWithoutName() {
    do_admin_login($this);

    $this->open("/admin/list_modules.php");
    $this->click("link=Create new Module");
    $this->waitForPageToLoad("30000");
    $this->type("name=modulecode", "S01SNE");
    $this->select("name=schoolid", "label=School of Selenium Testing");
    $this->click("name=submit");

    $this->assertTextPresent('Please enter a title for the module');
  }

  public function testCantCreateModuleWithoutSchool() {
    do_admin_login($this);

    $this->open("/admin/list_modules.php");
    $this->click("link=Create new Module");
    $this->waitForPageToLoad("30000");
    $this->type("name=modulecode", "S01SNE");
    $this->type("name=fullname", "Should Not Exist");
    $this->click("name=submit");

    $this->assertTextPresent('Please select a school for the module');
  }
}
?>