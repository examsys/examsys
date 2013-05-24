<?php
require_once 'shared.inc.php';

class ManageSchoolTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testCreateSchool() {
    do_admin_login($this);

    $this->open("/admin/index.php");
    $this->click("css=#14 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Schools' . $this->install_type);

    $this->click("link=Create new School");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Add Schools' . $this->install_type);

    $this->type('id=school', 'School of Short Lived');
    $this->select('name=facultyID', 'label=Faculty of Selenium Testing');
    $this->click('name=submit');
    $this->waitForPageToLoad('30000');
    $this->assertTextPresent('School of Short Lived');
  }

  /**
   * @depends testCreateSchool
   */
  public function testEditSchool() {
    do_admin_login($this);

    $this->open("/admin/list_schools.php");
    $this->doubleClick("css=#4 > td > div.col30");
    $this->waitForPageToLoad("30000");
    $this->type("id=school", "School of Short Lived2");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");

    $this->assertTextPresent('School of Short Lived2');
  }

  /**
   * @depends testCreateSchool
   */
  public function testDeleteSchool() {
    do_admin_login($this);

    $this->open("/admin/list_schools.php");
    $this->click("css=#4 > td > div.col30");
    $this->click("link=Delete School");
    $this->waitForPopUp("schools", "30000");
    $this->selectWindow("name=schools");
    $this->click("name=submit");
    $this->selectWindow('null');

    $this->open("/admin/list_schools.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextNotPresent('School of Short Lived2');
  }

  public function testCantCreateDuplicateSchool() {
    do_admin_login($this);

    $this->open("/admin/index.php");
    $this->click("css=#14 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Schools' . $this->install_type);

    $this->click("link=Create new School");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Add Schools' . $this->install_type);

    $this->type('id=school', 'School of Selenium Testing');
    $this->select('name=facultyID', 'label=Faculty of Selenium Testing');
    $this->click('name=submit');
    $this->waitForPageToLoad('30000');
    $this->assertTextPresent('School names must be unique within a faculty');
    $this->assertLocation($this->page_root . '/admin/add_school.php');
  }

  public function testCantCreateSchoolWithoutName() {
    do_admin_login($this);

    $this->open("/admin/list_schools.php");
    $this->click("link=Create new School");
    $this->waitForPageToLoad("30000");
    $this->click("name=submit");
    $this->assertEquals("Please enter name for the school.", $this->getAlert());

    $this->type("id=school", "");
    $this->click("name=submit");
    $this->assertEquals("Please enter name for the school.", $this->getAlert());
  }
}
?>