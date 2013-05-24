<?php
require_once 'shared.inc.php';

class ClassTotalsCalculationTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530215120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '0.00%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '18');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '18');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res4"]/td[5]', '12');
    $this->assertElementContainsText('//tr[@id="res4"]/td[6]', '66.67%');
    $this->assertElementContainsText('//tr[@id="res4"]/td[7]', 'Pass');

    $this->assertElementContainsText('//tr[@id="res5"]/td[5]', '-4.5');
    $this->assertElementContainsText('//tr[@id="res5"]/td[6]', '-25.00%');
    $this->assertElementContainsText('//tr[@id="res5"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '9');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '50.00%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Pass');

    // Overall

    // Failures
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[3]/td[2]', '2');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[3]/td[3]', '(33% of cohort)');
    // Passes
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[4]/td[2]', '2');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[4]/td[3]', '(33% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[5]/td[2]', '2');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[5]/td[3]', '(33% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[6]/td[2]', '18');
    // Mean
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[8]/td[2]', '8.8');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[8]/td[3]', '(48.61%)');
    // Median
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[9]/td[2]', '10.5');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[9]/td[3]', '(58.33%)');
    // Standard Deviation
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[10]/td[2]', '9.32');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[10]/td[3]', '(51.75%)');
    // Max
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[11]/td[2]', '18');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[11]/td[3]', '(100.00%)');
    // Min
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[12]/td[2]', '-4.5');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[12]/td[3]', '(-25.00%)');
    // Range
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[13]/td[2]', '22.5');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[13]/td[3]', '(125.00%)');

    // Deciles
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr/td[2]', '100.00%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[2]/td[2]', '100.00%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[3]/td[2]', '83.33%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[4]/td[2]', '66.67%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[5]/td[2]', '58.33%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[6]/td[2]', '50.00%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[7]/td[2]', '25.00%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[8]/td[2]', '0.00%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[9]/td[2]', '-12.50%');

    // Quartiles
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr/td[2]', '12.50%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr[2]/td[2]', '58.33%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr[3]/td[2]', '91.67%');
  }
}
?>