<?php
require_once 'shared.inc.php';

class ManageCourseTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testCreateCourse() {
    do_admin_login($this);

    $this->open("/admin/index.php");
    $this->click("css=#6 > tbody > tr > td > img");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Courses' . $this->install_type);

    $this->click("link=Create new Course");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Create new Course');

    $this->type("name=course", "SL100");
    $this->type("name=description", "Short Lived Course");
    $this->select("name=school", "label=School of Selenium Testing");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Short Lived Course');
  }

  /**
   * @depends testCreateCourse
   */
  public function testEditCourse() {
    do_admin_login($this);

    $this->open("/admin/list_courses.php");
    $this->doubleClick("css=#2 > td.col");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Edit Course');

    $this->type("name=course", "SL101");
    $this->type("name=description", "Short Lived Course2");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");

    $this->assertTextPresent('SL101');
    $this->assertTextPresent('Short Lived Course2');
  }

  /**
   * @depends testCreateCourse
   */
  public function testDeleteCourse() {
    do_admin_login($this);

    $this->open("/admin/list_courses.php");
    $this->click("css=#2 > td.col");
    $this->click("link=Delete Course");
    $this->waitForPopUp("notice", "30000");
    $this->selectWindow("name=notice");
    $this->click("name=submit");
    $this->selectWindow('null');

    $this->open("/admin/list_courses.php");
    $this->waitForPageToLoad("30000");
    $this->assertTextNotPresent('Short Lived Course2');
  }

  public function testCantCreateDuplicateCourse() {
    do_admin_login($this);

    $this->open("/admin/list_courses.php");
    $this->click("link=Create new Course");
    $this->waitForPageToLoad("30000");
    $this->type("name=course", "S100");
    $this->type("name=description", "Selenium Testing");
    $this->select("name=school", "label=School of Selenium Testing");
    $this->click("name=submit");
    $this->waitForPageToLoad("30000");

    $this->assertElementPresent('css=input.warn');
    $this->assertLocation($this->page_root . '/admin/add_course.php');
  }

  public function testCantCreateCourseWithoutRequiredFields() {
    do_admin_login($this);

    $this->open("/admin/list_courses.php");
    $this->click("link=Create new Course");
    $this->waitForPageToLoad("30000");
    $this->type("name=description", "Test Course");
    $this->select("name=school", "label=School of Selenium Testing");
    $this->click("name=submit");
    $this->assertEquals("Please enter a code for the course.", $this->getAlert());
    $this->type("name=course", "S200");
    $this->type("name=description", "");
    $this->click("name=submit");
    $this->assertEquals("Please enter a title for the course.", $this->getAlert());

    // TODO: Is School required?
  }
}
?>