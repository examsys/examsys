<?php
require_once 'shared.inc.php';

class ClassTotalsDichotomousTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530208110000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '0%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '168');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '-54');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '-32%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res4"]/td[5]', '50.5');
    $this->assertElementContainsText('//tr[@id="res4"]/td[6]', '30%');
    $this->assertElementContainsText('//tr[@id="res4"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res5"]/td[5]', '7');
    $this->assertElementContainsText('//tr[@id="res5"]/td[6]', '4%');
    $this->assertElementContainsText('//tr[@id="res5"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '17.5');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '10%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Fail');

    // Overall

    // Failures
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[3]/td[2]', '5');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[3]/td[3]', '(83% of cohort)');
    // Passes
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[4]/td[2]', '0');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[4]/td[3]', '(0% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[5]/td[2]', '1');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[5]/td[3]', '(17% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[6]/td[2]', '168');
    // Mean
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[8]/td[2]', '31.5');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[8]/td[3]', '(18.7%)');
    // Median
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[9]/td[2]', '7');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[9]/td[3]', '(4%)');
    // Standard Deviation
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[10]/td[2]', '74.96');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[10]/td[3]', '(44.6%)');
    // Max
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[11]/td[2]', '168');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[11]/td[3]', '(100%)');
    // Min
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[12]/td[2]', '-54');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[12]/td[3]', '(-32%)');
    // Range
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[13]/td[2]', '222');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[13]/td[3]', '(132%)');
    // Top 10%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[14]/td[2]', '65%');
    // Top 15%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[15]/td[2]', '47.5%');
    // Top 20%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[16]/td[2]', '30%');
    // Top 25%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[17]/td[2]', '25%');
    // Bottom 10%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[18]/td[2]', '-16%');
  }
}
?>