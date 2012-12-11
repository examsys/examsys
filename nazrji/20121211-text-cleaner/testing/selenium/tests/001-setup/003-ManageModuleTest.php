<?php
require_once 'shared.inc.php';

class ManageModuleTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type = ' \(local\)';

  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("https://rogo.local/");
  }

  public function testCreateModule()
  {
    do_admin_login($this);

    $this->open("/admin/index.php");
    $this->click("css=#10 > tbody > tr > td > img");
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
    $this->waitForPageToLoad("30000");

    $this->assertTextNotPresent('Should Not Exist');
  }


  public function testCantCreateModuleWithoutName() {
    do_admin_login($this);

    $this->open("/admin/list_modules.php");
    $this->click("link=Create new Module");
    $this->waitForPageToLoad("30000");
    $this->type("name=modulecode", "S01SNE");
    $this->select("name=schoolid", "label=School of Selenium Testing");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");

    $this->assertTextNotPresent('S01SNE');
  }

  // TODO: Can't create module without school
  // NOTE: Not possible - even if module is created without a valid school ID it will not appear in modules list
}
?>