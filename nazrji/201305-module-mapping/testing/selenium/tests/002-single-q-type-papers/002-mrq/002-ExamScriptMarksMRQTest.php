<?php
require_once 'shared.inc.php';

class ExamScrtiptMarksMRQTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=2&startdate=20120113000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-07 15:38:40',104,'0','n','n','-33.33',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '-2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '-9 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '-33.33%');
  }

  public function testAllCorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=2&startdate=20120113000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2012-12-14 11:13:31',105,'0','n','n','100.00',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '3 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '27 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '100.00%');
  }

  public function testAllIncorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=2&startdate=20120113000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
        $this->click("//span[@onclick=\"popMenu('2013-01-07 15:38:40',104,'0','n','n','-33.33',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '-2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '-9 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '-33.33%');
  }

  public function testMixed() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=2&startdate=20120113000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2012-12-14 11:39:48',107,'0','n','n','12.96',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '1 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0.5 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '3.5 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '12.96%');
  }

  public function testPartialAnswers() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=2&startdate=20120113000000&enddate=20530218110000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-18 09:29:13',108,'0','n','n','-12.96',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '1 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '-2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '1 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0.5 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '-3.5 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '-12.96%');
  }
}
?>