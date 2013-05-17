<?php
require_once 'shared.inc.php';

class SetupUserTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testCreateUser() {
    // TODO: this should be different user to standard staff user
    do_admin_login($this);

    $this->open("/staff/");
    $this->click("link=User Management");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Rogō: User Management' . $this->install_type);

    $this->click("link=Create new user");
    $this->waitForPageToLoad("30000");
    $this->assertTitle('Rogō: Create New User' . $this->install_type);

    $this->type("id=new_first_names", "Testing");
    $this->type("id=new_surname", "Staff2");
    $this->type("id=new_email", "teststaff2@test.com");
    $this->type("id=new_username", "teststaff2");
    $this->type("id=new_password", "VN4wExNHKN");
    $this->select("id=new_grade", "label=Academic Lecturer");
    $this->select("id=new_gender", "label=Male");
    $this->click("css=td > input[name=\"submit\"]");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('New account created for Mr Staff2');
  }

  // TODO: Can this actually be done?  Don't think Selenium can cope with alerts generated on page load
  // /**
  //  * @depends testCreateUser
  //  */
  // public function testCantCreateExistingUser()
  // {
  //   do_admin_login($this);

  //   $this->open("/staff/");
  //   $this->click("link=User Management");
  //   $this->waitForPageToLoad("30000");
  //   $this->assertTitle('Rogō: User Management' . $this->install_type);

  //   $this->click("link=Create new user");
  //   $this->waitForPageToLoad("30000");
  //   $this->assertTitle('Rogō: Create New User' . $this->install_type);

  //   $this->type("id=new_first_names", "Testing");
  //   $this->type("id=new_surname", "Staff");
  //   $this->type("id=new_email", "teststaff@test.com");
  //   $this->type("id=new_username", "teststaff");
  //   $this->type("id=new_password", "lxn&98X21");
  //   $this->select("id=new_grade", "label=Academic Lecturer");
  //   $this->select("id=new_gender", "label=Male");
  //   $this->click("css=td > input[name=\"submit\"]");
  //   $this->waitForPageToLoad("30000");
  //   $this->keyPressNative("32");
  // }

  public function testCantCreateUserWithoutRequiredFields() {
    do_admin_login($this);

    $this->open("/users/create_new_user.php");
    $this->type("id=new_surname", "test");
    $this->type("id=new_email", "test@test.com");
    $this->type("id=new_username", "test");
    $this->type("id=new_password", "test");
    $this->select("id=new_grade", "label=Academic Lecturer");
    $this->click("css=td > input[name=\"submit\"]");
    $this->assertEquals("Please enter the user's First names.", $this->getAlert());
    $this->type("id=new_first_names", "test");
    $this->type("id=new_surname", "");
    $this->click("css=td > input[name=\"submit\"]");
    $this->assertEquals("Please enter the user's Surname.", $this->getAlert());
    $this->type("id=new_surname", "test");
    $this->type("id=new_email", "");
    $this->click("css=td > input[name=\"submit\"]");
    $this->assertEquals("Please enter the user's Email Address.", $this->getAlert());
    $this->type("id=new_email", "test@test.com");
    $this->type("id=new_username", "");
    $this->click("css=td > input[name=\"submit\"]");
    $this->assertEquals("Please enter a Username for the user.", $this->getAlert());
    $this->type("id=new_username", "test");
    $this->type("id=new_password", "");
    $this->click("css=td > input[name=\"submit\"]");
    $this->assertEquals("Please enter a default Password for the user.", $this->getAlert());
    $this->type("id=new_password", "test");
    $this->select("id=new_grade", "label=");
    $this->click("css=td > input[name=\"submit\"]");
    $this->assertEquals("Please enter a Type/Course for the user.", $this->getAlert());
  }

  // TODO: Check valid email address?

  /**
   * @depends testCreateUser
   */
  public function testAddUserToTeam() {
    do_admin_login($this);

    $this->open("/users/details.php?userID=154");
    $this->click("//td[@onclick=\"showTab('Teams_tab')\"]");
    $this->click("link=Edit Teams...");
    $this->waitForPopUp("editmodule", "30000");
    $this->selectWindow("name=editmodule");
    $this->assertTitle('Manage Teams' . $this->install_type);

    $this->click("id=mod0");
    $this->click("name=submit");
    $this->assertTextPresent('S01SET');
  }

  /**
   * @depends testAddUserToTeam
   */
  public function testUserAppearsInTeamList() {
    do_admin_login($this);

    $this->open("/staff/");
    $this->click("css=strong");
    $this->waitForPageToLoad("30000");
    $this->click("link=School of Selenium Testing");
    $this->click("link=exact:S01SET: Selenium Testing");
    $this->waitForPageToLoad("30000");
    $this->assertElementContainsText("//div[8]/form/div/ul/li[2]/a", 'Staff2, T. Mr');
  }

  /**
   * @depends testCreateUser
   */
  public function testUserCanLogIn() {
    do_staff_login($this, 'teststaff2', 'VN4wExNHKN');
    $this->assertTextPresent('My Modules');
  }

  // TODO: edit user
  // TODO: remove user from team
  // TODO: delete user
  // TODO: create student user
  // TODO: add student to module
  // TODO: remove student from module
  // TODO: delete student
}
?>