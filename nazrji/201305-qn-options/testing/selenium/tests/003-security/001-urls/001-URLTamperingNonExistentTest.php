<?php
require_once 'shared.inc.php';

class URLTamperingNonExistentTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  // public function testAddToTeamNonExistent() {
  //   do_staff_login($this);

  //   $this->open("/folder/edit_team_popup.php?module=888207&calling=paper_list&folder=");
  //   $this->assertTextPresent('Page not Found');
  // }

  // // public function testLogLateNonExistentDate() {
  // //   do_staff_login($this);

  // //   $this->open("/folder/edit_team_popup.php?module=888207&calling=paper_list&folder=");
  // //   $this->assertTextPresent('Page not Found');
  // // }

  // // public function testLogLateNonExistentPaper() {
  // //   do_staff_login($this);

  // //   $this->open("/folder/edit_team_popup.php?module=888207&calling=paper_list&folder=");
  // //   $this->assertTextPresent('Page not Found');
  // // }

  // // public function testLogLateNonExistentUser() {
  // //   do_staff_login($this);

  // //   $this->open("/folder/edit_team_popup.php?module=888207&calling=paper_list&folder=");
  // //   $this->assertTextPresent('Page not Found');
  // // }

  // public function testRetirePaperNonExistent() {
  //   do_staff_login($this);

  //   $this->open("paper/check_retire_paper.php?paperID=8888888888&module=3&folder=");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteLTIKeyNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_LTIkeys.php?LTIkeysID=888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteSchoolNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_school.php?schoolID=888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteAnnouncementNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_announcement.php?announcementID=8888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteEbelGridNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_ebel_template.php?gridID=888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteFacultyNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_faculty.php?facultyID=8888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteFolderNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_folder.php?folderID=888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteKeywordNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_team_keyword.php?keywordID=,88888888&module=");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteLabNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_lab.php?labID=888888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteModuleNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_module.php?idMod=8888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteReferenceNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_ref_material.php?refID=888888888888&module=3");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeleteUserNonExistent() {
  //   do_admin_login($this);

  //   $this->open("delete/check_delete_user.php?id=,3,4,8888888,5");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testClassTotalsNonExistent() {
  //   do_staff_login($this);

  //   $this->open("reports/class_totals.php?paperID=885380&startdate=20120717100000&enddate=20120717140000&repmodule=&repcourse=%&sortby=name&module=240&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testCopyOntoPaperNonExistent() {
  //   do_staff_login($this);

  //   $this->open("question/copy_onto_paper.php?q_id=,888870970");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testDeletePaperNonExistent() {
  //   do_staff_login($this);

  //   $this->open("delete/check_delete_paper.php?paperID=88883011&module=3&folder=");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditEbelGridNonExistent() {
  //   do_admin_login($this);

  //   $this->open("admin/edit_ebel_grid.php?id=88888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditAnnouncementNonExistent() {
  //   do_admin_login($this);

  //   $this->open("admin/edit_announcement.php?announcementid=888888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditCourseNonExistent() {
  //   do_admin_login($this);

  //   $this->open("admin/edit_course.php?courseID=88888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditFacultyNonExistent() {
  //   do_admin_login($this);

  //   $this->open("admin/edit_faculty.php?facultyID=888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditLabNonExistent() {
  //   do_admin_login($this);

  //   $this->open("admin/edit_lab.php?labID=888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditModuleNonExistent() {
  //   do_admin_login($this);

  //   $this->open("admin/edit_module.php?moduleid=8888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditReferenceNonExistent() {
  //   do_admin_login($this);

  //   $this->open("folder/edit_ref_material.php?refID=88888888&module=3");
  //   $this->assertTextPresent('Page not Found');
  // }

  // public function testEditSchoolNonExistent() {
  //   do_admin_login($this);

  //   $this->open("admin/edit_school.php?schoolid=8888888");
  //   $this->assertTextPresent('Page not Found');
  // }

  public function testFolderDetailsNonExistent() {
    do_staff_login($this);

    $this->open("folder/details.php?folder=888888888");
    $this->assertTextPresent('Page not Found');
  }

  public function testFolderPropsNonExistent() {
    do_staff_login($this);

    $this->open("folder/properties.php?folder=888888888");
    $this->assertTextPresent('Page not Found');
  }

  public function testHelpStaffEditNonExistent() {
    do_admin_login($this);

    $this->open("help/staff/edit_page.php?id=888888888");
    $this->assertTextPresent('Page not Found');
  }
}
?>