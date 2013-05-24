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

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20530208110000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '0.00%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '168');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '-54');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '-32.14%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res4"]/td[5]', '50.5');
    $this->assertElementContainsText('//tr[@id="res4"]/td[6]', '30.06%');
    $this->assertElementContainsText('//tr[@id="res4"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res5"]/td[5]', '7');
    $this->assertElementContainsText('//tr[@id="res5"]/td[6]', '4.17%');
    $this->assertElementContainsText('//tr[@id="res5"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '17.5');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '10.42%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Fail');

    // Overall

    // Failures
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[3]/td[2]', '5');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[3]/td[3]', '(83% of cohort)');
    // Passes
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[4]/td[2]', '0');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[4]/td[3]', '(0% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[5]/td[2]', '1');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[5]/td[3]', '(17% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[6]/td[2]', '168');
    // Mean
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[8]/td[2]', '31.5');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[8]/td[3]', '(18.75%)');
    // Median
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[9]/td[2]', '12.3');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[9]/td[3]', '(7.29%)');
    // Standard Deviation
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[10]/td[2]', '74.96');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[10]/td[3]', '(44.62%)');
    // Max
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[11]/td[2]', '168');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[11]/td[3]', '(100.00%)');
    // Min
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[12]/td[2]', '-54');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[12]/td[3]', '(-32.14%)');
    // Range
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[13]/td[2]', '222');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[13]/td[3]', '(132.14%)');

    // Deciles
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr/td[2]', '65.03%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[2]/td[2]', '30.06%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[3]/td[2]', '20.24%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[4]/td[2]', '10.42%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[5]/td[2]', '7.29%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[6]/td[2]', '4.17%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[7]/td[2]', '2.08%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[8]/td[2]', '0%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[9]/td[2]', '-16.07%');

    // Quartiles
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr/td[2]', '1.04%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr[2]/td[2]', '7.29%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr[3]/td[2]', '25.15%');
  }
}