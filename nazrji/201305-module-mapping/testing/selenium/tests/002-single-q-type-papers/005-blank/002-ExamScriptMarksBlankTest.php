<?php
require_once 'shared.inc.php';

class ExamScrtiptMarksBlankTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=5&startdate=20130101000000&enddate=20530110170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-10 12:19:53',104,'0','n','n','0.00',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '0 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '0 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '0 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '0.00%');
  }

  public function testAllCorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=5&startdate=20130101000000&enddate=20530210170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-10 13:18:16',105,'0','n','n','100.00',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '6 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '3 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '6 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '3 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '26 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '100.00%');
  }

  public function testAllIncorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=5&startdate=20130101000000&enddate=20530210170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-10 13:57:35',106,'0','n','n','-30.77',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '-1 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '-1 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '-8 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '-30.77%');
  }

  public function testMixed1() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=5&startdate=20130101000000&enddate=20530210170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-10 15:05:39',107,'0','n','n','11.54',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '0 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '-1 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '1 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '3 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '-1 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '3 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '11.54%');
  }

  public function testMixed2() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=5&startdate=20130101000000&enddate=20530210170000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-10 15:14:20',108,'0','n','n','0.00',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '-1 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '-1 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '0 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '0.00%');
  }
}
?>