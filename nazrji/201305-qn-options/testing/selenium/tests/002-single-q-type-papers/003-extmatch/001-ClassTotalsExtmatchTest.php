<?php
require_once 'shared.inc.php';

class ClassTotalsExtmatchTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '0%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '162');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '-47.5');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '-29%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res4"]/td[5]', '47.5');
    $this->assertElementContainsText('//tr[@id="res4"]/td[6]', '29%');
    $this->assertElementContainsText('//tr[@id="res4"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res5"]/td[5]', '14');
    $this->assertElementContainsText('//tr[@id="res5"]/td[6]', '9%');
    $this->assertElementContainsText('//tr[@id="res5"]/td[7]', 'Fail');

    // Overall

    // Failures
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[3]/td[2]', '4');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[3]/td[3]', '(80% of cohort)');
    // Passes
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[4]/td[2]', '0');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[4]/td[3]', '(0% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[5]/td[2]', '1');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[5]/td[3]', '(20% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[6]/td[2]', '162');
    // Mean
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[8]/td[2]', '35.2');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[8]/td[3]', '(21.8%)');
    // Median
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[9]/td[2]', '0');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[9]/td[3]', '(0%)');
    // Standard Deviation
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[10]/td[2]', '78.67');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[10]/td[3]', '(48.4%)');
    // Max
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[11]/td[2]', '162');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[11]/td[3]', '(100%)');
    // Min
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[12]/td[2]', '-47.5');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[12]/td[3]', '(-29%)');
    // Range
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[13]/td[2]', '209.5');
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[13]/td[3]', '(129%)');
    // Top 10%
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[14]/td[2]', '71.6%');
    // Top 15%
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[15]/td[2]', '57.4%');
    // Top 20%
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[16]/td[2]', '43.2%');
    // Top 25%
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[17]/td[2]', '29%');
    // Bottom 10%
    $this->assertElementContainsText('//table/tbody/tr[22]/td/table/tbody/tr[18]/td[2]', '-17.4%');
  }
}
?>