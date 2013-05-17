<?php
require_once 'shared.inc.php';

class ManageFacultyTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testCreateFaculty() {
    do_admin_login($this);

    $this->create_faculty('Faculty of Short Lived');
  }

  // TODO: Can't create faculty with same name
  // NOTE: not possible with current implementation

  /**
   * @depends testCreateFaculty
   */
  public function testEditFaculty() {
    do_admin_login($this);

    $this->open("/admin/list_faculties.php");
    // TODO: investigate why double click doesn't work in Selenium but using the 'Edit Faculty' link does
    // $this->doubleClick("css=#4 > td > div.col10");
    $this->click("css=#4 > td > div.col10");
    $this->click("link=Edit Faculty");
    $this->waitForPopUp("faculties", "30000");
    $this->selectWindow("name=faculties");
    $this->type("name=new_faculty", "Faculty of Short Lived2");
    $this->click("name=submit");
    $this->selectWindow('null');

    $this->open("/admin/list_faculties.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Faculty of Short Lived2');
  }

  /**
   * @depends testCreateFaculty
   */
  public function testDeleteFaculty() {
    do_admin_login($this);

    $this->open("/admin/list_faculties.php");
    $this->click("css=#4 > td > div.col10");
    $this->click("link=Delete Faculty");
    $this->waitForPopUp("faculties", "30000");
    $this->selectWindow("name=faculties");
    $this->click("name=submit");
    $this->selectWindow('null');

    $this->open("/admin/list_faculties.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextNotPresent('Faculty of Short Lived2');
  }

  private function create_faculty($name) {
    $this->open("/staff/index.php");
    $this->click("link=Administrative Tools");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Rogō: Admin' . $this->install_type);

    $this->click("css=#9 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Faculties' . $this->install_type);

    $this->click("link=Create new Faculty");
    $this->waitForPopUp("faculties", "30000");
    $this->selectWindow("name=faculties");
    $this->type("name=add_faculty", $name);
    $this->click("name=ok");
    $this->selectWindow('null');

    $this->open("/admin/list_faculties.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent($name);
  }

  public function testCantCreateFacultyWithoutName() {
    do_admin_login($this);

    $this->open("/admin/list_faculties.php");
    $this->click("link=Create new Faculty");
    $this->waitForPopUp("faculties", "30000");
    $this->selectWindow("name=faculties");
    $this->click("name=ok");
    $this->selectWindow('null');

    $this->open("/admin/list_faculties.php");
    $this->waitForPageToLoad("30000");
    $this->assertCssCount('css=tr.l', 3);
  }
}
?>