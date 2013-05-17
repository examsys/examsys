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

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20530215120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '0%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '18');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '18');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res4"]/td[5]', '12');
    $this->assertElementContainsText('//tr[@id="res4"]/td[6]', '67%');
    $this->assertElementContainsText('//tr[@id="res4"]/td[7]', 'Pass');

    $this->assertElementContainsText('//tr[@id="res5"]/td[5]', '-4.5');
    $this->assertElementContainsText('//tr[@id="res5"]/td[6]', '-25%');
    $this->assertElementContainsText('//tr[@id="res5"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '9');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '50%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Pass');

    // Overall

    // Failures
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[3]/td[2]', '2');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[3]/td[3]', '(33% of cohort)');
    // Passes
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[4]/td[2]', '2');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[4]/td[3]', '(33% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[5]/td[2]', '2');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[5]/td[3]', '(33% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[6]/td[2]', '18');
    // Mean
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[8]/td[2]', '8.8');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[8]/td[3]', '(48.7%)');
    // Median
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[9]/td[2]', '9');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[9]/td[3]', '(50%)');
    // Standard Deviation
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[10]/td[2]', '9.32');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[10]/td[3]', '(51.8%)');
    // Max
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[11]/td[2]', '18');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[11]/td[3]', '(100%)');
    // Min
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[12]/td[2]', '-4.5');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[12]/td[3]', '(-25%)');
    // Range
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[13]/td[2]', '22.5');
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[13]/td[3]', '(125%)');
    // Top 10%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[14]/td[2]', '100%');
    // Top 15%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[15]/td[2]', '100%');
    // Top 20%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[16]/td[2]', '100%');
    // Top 25%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[17]/td[2]', '91.75%');
    // Bottom 10%
    $this->assertElementContainsText('//table/tbody/tr[23]/td/table/tbody/tr[18]/td[2]', '-12.5%');
  }
}
?>