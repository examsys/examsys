<?php
require_once 'shared.inc.php';

class ExamScrtiptMarksCalculationTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530115120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-14 14:30:17',104,'0','n','n','0',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '0 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '0 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '0%');
  }

  public function testAllCorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530115120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-15 09:48:40',105,'0','n','n','100',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '2 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '18 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '100%');
  }

  public function testAllCorrectWithTolerance() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530115120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-15 09:53:30',106,'0','n','n','100',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[2]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[3]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[4]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 5%');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 5%');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '18 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '100%');
  }

  public function testPartial() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530115120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-15 09:58:08',107,'0','n','n','67',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0.5 out of 1');
    $text = $this->getText('//table[3]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[3]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1.5$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '0.5 out of 1');
    $text = $this->getText('//table[4]/tbody/tr[2]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[4]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1.5$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0.5 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 5%');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 8%');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '0.5 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 5%');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 8%');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '12 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '67%');
  }

  public function testAllIncorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530115120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-15 10:41:51',108,'0','n','n','-25',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '-1 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '-4.5 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '-25%');
  }

  public function testMixed() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530115120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-15 11:08:17',109,'0','n','n','50',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[3]/tbody/tr/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0.5 out of 1');
    $text = $this->getText('//table[3]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[4]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 8%');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]', 'with a tolerance of 8%');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '9 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '50%');
  }
}
?>