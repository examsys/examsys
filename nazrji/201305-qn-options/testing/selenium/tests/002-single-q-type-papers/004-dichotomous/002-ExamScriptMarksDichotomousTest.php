<?php
require_once 'shared.inc.php';

class ExamScrtiptMarksDichotomousTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530108160000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-08 10:06:23',104,'0','n','n','0',event);\"]");
    $this->click("id=item1a");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");


    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '0 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '0 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '0%');
  }

  public function testAllCorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530108160000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-08 12:08:11',105,'0','n','n','100',event);\"]");
    $this->click("css=#item1a > img");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '168 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '100%');
  }

  public function testAllIncorrect() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530108160000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-08 13:37:14',106,'0','n','n','-32',event);\"]");
    $this->click("css=#item1a > img");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '-54 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '-32%');
  }

  public function testMixed() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530108160000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-08 14:43:09',107,'0','n','n','30',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '3 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '1 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '2 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '-1 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '3 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '1 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '2 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '1 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '50.5 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '30%');
  }

  public function testAbstentions() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530209160000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-08 17:01:11',108,'0','n','n','4',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '0.5 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '3 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '0 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '7 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '4%');
  }

  public function testPartialAnswers() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530209160000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");
    $this->click("//span[@onclick=\"popMenu('2013-01-18 16:36:04',109,'0','n','n','10',event);\"]");
    $this->click("id=item1b");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '1.5 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '6 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-4 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '2 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '1.5 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '6 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '-4 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '1 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '1.5 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '-4 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '2 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '-3 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '17.5 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '10%');
  }
}
?>