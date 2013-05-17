<?php
require_once 'shared.inc.php';

class ClassTotalsMCQTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testResults() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=1&startdate=20120111000000&enddate=20530113100000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '0%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '12');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '-4');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '-33%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res4"]/td[5]', '5');
    $this->assertElementContainsText('//tr[@id="res4"]/td[6]', '42%');
    $this->assertElementContainsText('//tr[@id="res4"]/td[7]', 'Pass');

    // Overall

    // Failures
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[3]/td[2]', '2');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[3]/td[3]', '(50% of cohort)');
    // Passes
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[4]/td[2]', '1');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[4]/td[3]', '(25% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[5]/td[2]', '1');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[5]/td[3]', '(25% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[6]/td[2]', '12');
    // Mean
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[8]/td[2]', '3.3');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[8]/td[3]', '(27.3%)');
    // Median
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[9]/td[2]', '0');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[9]/td[3]', '(0%)');
    // Standard Deviation
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[10]/td[2]', '6.90');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[10]/td[3]', '(57.4%)');
    // Max
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[11]/td[2]', '12');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[11]/td[3]', '(100%)');
    // Min
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[12]/td[2]', '-4');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[12]/td[3]', '(-33%)');
    // Range
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[13]/td[2]', '16');
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[13]/td[3]', '(133%)');
    // Top 10%
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[14]/td[2]', '82.6%');
    // Top 15%
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[15]/td[2]', '73.9%');
    // Top 20%
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[16]/td[2]', '65.2%');
    // Top 25%
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[17]/td[2]', '56.5%');
    // Bottom 10%
    $this->assertElementContainsText('//table/tbody/tr[21]/td/table/tbody/tr[18]/td[2]', '-23.1%');
  }
}
?>