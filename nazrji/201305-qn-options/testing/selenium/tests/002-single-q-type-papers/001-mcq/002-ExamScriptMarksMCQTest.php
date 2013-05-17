<?php
require_once 'shared.inc.php';

class ExamScrtiptMarksMCQTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testUnanswered() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=1&startdate=20120111000000&enddate=20530113150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-07 14:45:44',104,'0','n','n','0',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '0 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '0 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '0%');
  }

  public function testAllCorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=1&startdate=20120111000000&enddate=20530113150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2012-12-12 12:12:42',105,'0','n','n','100',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '2 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '12 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '100%');
  }

  public function testAllIncorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=1&startdate=20120111000000&enddate=20530113150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2012-12-12 12:12:50',106,'0','n','n','-33',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '-2 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '-1 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '-4 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '-33%');
  }

  public function testMixed() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=1&startdate=20120111000000&enddate=20530113150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2012-12-12 12:12:57',107,'0','n','n','42',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '2 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '5 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '42%');
  }
}
?>