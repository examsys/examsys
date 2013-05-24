<?php
require_once 'shared.inc.php';

class ExamScrtiptMarksExtmatchTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-07 16:11:50',104,'0','n','n','0.00',event);\"]");
    $this->click("css=#item1a > img");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 5');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 14');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 14');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 6');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 16');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 16');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '0 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[19]/table/tbody/tr[2]/td[2]', '0 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[4]/td[2]', '0.00%');
  }

  public function testAllCorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-04 14:25:21',105,'0','n','n','100.00',event);\"]");
    $this->click("css=#item1a > img");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '5 out of 5');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '14 out of 14');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '14 out of 14');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p[2]/span', '1 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '6 out of 6');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '16 out of 16');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '6 out of 6');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '6 out of 6');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '16 out of 16');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '6 out of 6');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p[2]/span', '1 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[19]/table/tbody/tr[2]/td[2]', '162 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[4]/td[2]', '100.00%');
  }

  public function testAllIncorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-07 10:00:38',106,'0','n','n','-29.32',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '-1.5 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '-3 out of 3');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 5');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 14');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '-2.5 out of 5');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '-7 out of 14');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 6');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 16');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '-3 out of 6');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '-8 out of 16');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '-6 out of 6');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[19]/table/tbody/tr[2]/td[2]', '-47.5 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[4]/td[2]', '-29.32%');
  }

  public function testMixed() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-07 11:13:38',107,'0','n','n','29.32',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '1 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '2 out of 8');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '1 out of 3');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '3 out of 3');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 5');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '8 out of 14');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '3 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '2 out of 5');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '2 out of 14');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '1 out of 5');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 6');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '8 out of 16');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '2 out of 6');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '1.5 out of 6');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '4 out of 16');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[19]/table/tbody/tr[2]/td[2]', '47.5 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[4]/td[2]', '29.32%');
  }

  public function testPartialAnswers() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-18 10:43:40',108,'0','n','n','8.64',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText("//table[2]/tbody/tr[2]/td[2]/p[2]/span", '2 out of 3');
    $this->assertElementContainsText("//table[2]/tbody/tr[4]/td[2]/p/span", '0 out of 8');
    $this->assertElementContainsText("//table[3]/tbody/tr/td[2]/p/span", '1 out of 3');
    $this->assertElementContainsText("//table[3]/tbody/tr[4]/td[2]/p/span", '-1 out of 3');
    $this->assertElementContainsText("//table[4]/tbody/tr/td[2]/p/span", '-3 out of 8');
    $this->assertElementContainsText("//table[4]/tbody/tr[3]/td[2]/p/span", '2 out of 3');
    $this->assertElementContainsText("//table[5]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[5]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[6]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[6]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[7]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[7]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[8]/tbody/tr[2]/td[2]/p[2]/span", '3 out of 5');
    $this->assertElementContainsText("//table[8]/tbody/tr[4]/td[2]/p/span", '0 out of 14');
    $this->assertElementContainsText("//table[9]/tbody/tr/td[2]/p/span", '2 out of 5');
    $this->assertElementContainsText("//table[9]/tbody/tr[4]/td[2]/p/span", '-1.5 out of 5');
    $this->assertElementContainsText("//table[10]/tbody/tr/td[2]/p/span", '4 out of 14');
    $this->assertElementContainsText("//table[10]/tbody/tr[3]/td[2]/p/span", '3 out of 5');
    $this->assertElementContainsText("//table[11]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[11]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[12]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[12]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[13]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[13]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[14]/tbody/tr[2]/td[2]/p[2]/span", '4 out of 6');
    $this->assertElementContainsText("//table[14]/tbody/tr[4]/td[2]/p/span", '0 out of 16');
    $this->assertElementContainsText("//table[15]/tbody/tr/td[2]/p/span", '2 out of 6');
    $this->assertElementContainsText("//table[15]/tbody/tr[4]/td[2]/p/span", '-2 out of 6');
    $this->assertElementContainsText("//table[16]/tbody/tr/td[2]/p/span", '2 out of 16');
    $this->assertElementContainsText("//table[16]/tbody/tr[3]/td[2]/p/span", '4 out of 6');
    $this->assertElementContainsText("//table[17]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[17]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[18]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[18]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[19]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[19]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText("//div[19]/table/tbody/tr[2]/td[2]", '14 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText("//div[19]/table/tbody/tr[4]/td[2]", '8.64%');
  }
}
?>