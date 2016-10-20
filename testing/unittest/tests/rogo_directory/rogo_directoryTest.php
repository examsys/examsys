<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

use testing\unittest\UnitTest;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStream;

/**
 * Test rogo_directory class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class rogo_directorytest extends UnitTest {
  /** @var rogo_directory Stores a mock version of the rogo_directory class. */
  protected $rogodirectory;

  /** @var string The url we will use for Rogo for these tests. */
  protected $webroot = 'htttp://www.example.com/';

  public function setUp() {
    parent::setUp();
    $this->rogodirectory = $this->getMockForAbstractClass('rogo_directory');
    // Ensure the root path is set to a known value.
    $this->config->set('cfg_root_path', $this->webroot);
  }

  public function tearDown() {
    $this->rogodirectory = null;
    parent::tearDown();
  }

  /**
   * Tests the create method will create a directory, based on the return value of the location method.
   *
   * @group rogo_directory
   */
  public function test_create() {
    // Set the location of the rogo directory to be a sub direcotry called test.
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->rogodirectory->create();
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertFalse(vfsStreamWrapper::getRoot()->getChild('test')->hasChildren());
  }

  /**
   * Tests the create method will create a directory, where the directory location not directly in the main data location.
   *
   * @group rogo_directory
   */
  public function test_create_multilevel() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/test2/');
    $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->rogodirectory->create();
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChild('test2'));
  }

  /**
   * Tests the create throws an exception when the data root directoy that is not writable.
   *
   * @expectedException directory_not_found
   * @group rogo_directory
   */
  public function test_create_not_writable() {
    // Set the location of the rogo directory to be a sub direcotry called test.
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0000); // Set the data directory to not be wriatable.
    $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->rogodirectory->create();
  }

  /**
   * Tests the create a directory with 700 permissions.
   *
   * @group rogo_directory
   */
  public function test_create_root_has_700_permissions() {
    // Set the location of the rogo directory to be a sub direcotry called test.
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0700);
    $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->rogodirectory->create();
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
  }

  /**
   * Tests the create method does not break when the directory it should make already exists.
   *
   * @group rogo_directory
   */
  public function test_create_when_directory_exists() {
    // Set the location of the rogo directory to be a sub direcotry called test.
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'subdirectory' => array(),
        'random' => array(),
        'testfile.txt' => 'test content',
      )
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->rogodirectory->create();
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChild('random'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChild('subdirectory'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChild('testfile.txt'));
  }

  /**
   * Tests the clear method will empty the directory, but not delete the directory itself.
   *
   * @group rogo_directory
   */
  public function test_clear() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'subdirectory' => array(),
        'random' => array(
          'testfile.txt' => 'test content',
        ),
        'testfile.txt' => 'test content',
      )
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertTrue($this->rogodirectory->clear());
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(0, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
  }

  /**
   * Tests the clear method will return false if server set to read only.
   *
   * @group rogo_directory
   */
  public function test_clear_empty() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    $structre = array(
      'test' => array(),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(0, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertTrue($this->rogodirectory->clear());
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(0, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
  }

  /**
   * Tests the clear method will return false if server set to read only.
   *
   * @group rogo_directory
   */
  public function test_clear_read_only() {
    $this->config->set('cfg_readonly_host', true);
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'subdirectory' => array(),
        'random' => array(
          'testfile.txt' => 'test content',
        ),
        'testfile.txt' => 'test content',
      )
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertFalse($this->rogodirectory->clear());
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
  }
  
  /**
   * Tests the clear method will not fail if the directory is already empty.
   *
   * @group rogo_directory
   */
  public function test_clear_empty_read_only() {
    $this->config->set('cfg_readonly_host', true);
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    $structre = array(
      'test' => array(),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(0, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertFalse($this->rogodirectory->clear());
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(0, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
  }
  
  
  /**
   * Tests the clear method will not fail if the directory does not exist.
   *
   * @group rogo_directory
   */
  public function test_clear_no_directory() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertFalse($this->rogodirectory->clear());
  }

  /**
   * Tests the clear method return false if it cannot clear the directory.
   *
   * @group rogo_directory
   */
  public function test_clear_no_permissions() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'subdirectory' => array(),
        'random' => array(
          'testfile.txt' => 'test content',
        ),
        'testfile.txt' => 'test content',
      )
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0000, $structre);
    vfsStreamWrapper::getRoot()->getChild('test')->chmod(0000);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertFalse($this->rogodirectory->clear());
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
  }

  /**
   * Tests the clear method return false if it cannot delete some entries.
   *
   * @group rogo_directory
   */
  public function test_clear_no_permissions2() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'subdirectory' => array(),
        'random' => array(
          'testfile.txt' => 'test content',
        ),
        'testfile.txt' => 'test content',
      )
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0000, $structre);
    vfsStreamWrapper::getRoot()->getChild('test')->getChild('random')->chmod(0000);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(3, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertFalse($this->rogodirectory->clear());
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertCount(1, vfsStreamWrapper::getRoot()->getChild('test')->getChildren());
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChild('random'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->getChild('random')->hasChild('testfile.txt'));
  }

  /**
   * Tests the verify_file method does not throw an exception if a file exists in the directory.
   *
   * @group rogo_directory
   */
  public function test_verify_file() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'testfile.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChild('testfile.txt'));
    $this->rogodirectory->verify_file('testfile.txt');
  }

  /**
   * Tests the verify_file method throws an exception if the file does not exist.
   *
   * @expectedException file_not_found
   * @group rogo_directory
   */
  public function test_verify_file_that_does_not_exist() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertFalse(vfsStreamWrapper::getRoot()->getChild('test')->hasChildren());
    $this->rogodirectory->verify_file('testfile.txt');
  }

  /**
   * Tests the verify_file method throws an exception if the file does not exist.
   *
   * @expectedException file_not_found
   * @group rogo_directory
   */
  public function test_verify_file_that_does_not_exist2() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'random.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChildren());
    $this->rogodirectory->verify_file('testfile.txt');
  }

  /**
   * Tests the verify_file method throws an exception if the file is not in the main directory.
   *
   * @expectedException file_not_found
   * @group rogo_directory
   */
  public function test_verify_file_that_does_not_exist3() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/test/');
    // The contents of the directory.
    $structre = array(
      'test' => array(
        'random' => array(
          'testfile.txt' => 'test content',
        ),
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('test')->hasChildren());
    $this->rogodirectory->verify_file('testfile.txt');
  }

  /**
   * Tests the check_permissions method returns true when the directory is usable.
   *
   * @group rogo_directory
   */
  public function test_check_permissions() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data'));
    $this->assertTrue($this->rogodirectory->check_permissions());
  }

  /**
   * Tests the check_permissions method returns true when the directory is usable (read only server).
   *
   * @group rogo_directory
   */
  public function test_check_permissions_read_only() {
    $this->config->set('cfg_readonly_host', true);
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data'));
    vfsStreamWrapper::getRoot()->chmod(0500);
    $this->assertTrue($this->rogodirectory->check_permissions());
  }
  
  /**
   * Tests the check_permissions method returns false when the directory is not writable.
   *
   * @group rogo_directory
   */
  public function test_check_permissions_not_writable() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data'));
    vfsStreamWrapper::getRoot()->chmod(0000);
    $this->assertFalse($this->rogodirectory->check_permissions());
  }

  /**
   * Tests the check_permissions method returns false when the directory is not writable (read only server).
   *
   * @group rogo_directory
   */
  public function test_check_permissions_not_writable_read_only() {
    $this->config->set('cfg_readonly_host', true);
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data'));
    $this->assertFalse($this->rogodirectory->check_permissions());
  }
  
  /**
   * Tests the check_permissions method returns false when the directory is not writable.
   *
   * @group rogo_directory
   */
  public function test_check_permissions_not_writable2() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data'));
    vfsStreamWrapper::getRoot()->chmod(0500);
    $this->assertFalse($this->rogodirectory->check_permissions());
  }

  /**
   * Tests the check_permissions method returns false when the directory is not readable.
   *
   * @group rogo_directory
   */
  public function test_check_permissions_not_readable() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data'));
    vfsStreamWrapper::getRoot()->chmod(0300); // Write and execute.
    $this->assertFalse($this->rogodirectory->check_permissions());
  }

  /**
   * Tests the check_permissions method returns true when the directory is not execuatble, has read and write.
   *
   * @group rogo_directory
   */
  public function test_check_permissions_not_executable() {
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data'));
    vfsStreamWrapper::getRoot()->chmod(0600); // Read and write.
    $this->assertTrue($this->rogodirectory->check_permissions());
  }

  /**
   * Test that getting a directory type that does not exist will fail.
   *
   * @expectedException directory_not_found
   * @group rogo_directory
   */
  public function test_get_directory_failure() {
    rogo_directory::get_directory('fakedirectorytype');
  }

  /**
   * Test that getting a directory type that does not exist will fail, even if a class of that name exists.
   *
   * @expectedException directory_not_found
   * @group rogo_directory
   */
  public function test_get_directory_failure2() {
    rogo_directory::get_directory('DBUtils');
  }

  /**
   * Tests that the url method generates a valid url for the rogo site, while using the default parameters.
   *
   * @group rogo_directory
   */
  public function test_url_defaults() {
    $structre = array(
      'test' => array(
        'testfile.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $url = $this->rogodirectory->url('testfile.txt');
    $this->assertContains($this->webroot . 'getfile.php?', $url);
    $this->assertContains('type=Mock_rogo_directory', $url);
    $this->assertContains('filename=testfile.txt', $url);
    $this->assertNotContains('forcedownload=1', $url);
  }

  /**
   * Tests that the url method generates a valid url for the rogo site.
   *
   * @group rogo_directory
   */
  public function test_url_no_forcedownload() {
    $structre = array(
      'test' => array(
        'testfile.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $url = $this->rogodirectory->url('testfile.txt', false);
    $this->assertContains($this->webroot . 'getfile.php?', $url);
    $this->assertContains('type=Mock_rogo_directory', $url);
    $this->assertContains('filename=testfile.txt', $url);
    $this->assertNotContains('forcedownload=1', $url);
  }

  /**
   * Tests that the url method generates a valid url for the rogo site.
   *
   * @group rogo_directory
   */
  public function test_url_forcedownload() {
    $structre = array(
      'test' => array(
        'testfile.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $url = $this->rogodirectory->url('testfile.txt', true);
    $this->assertContains($this->webroot . 'getfile.php?', $url);
    $this->assertContains('type=Mock_rogo_directory', $url);
    $this->assertContains('filename=testfile.txt', $url);
    $this->assertContains('forcedownload=1', $url);
  }

  /**
   * Tests that the url method generates a valid url for a file that does not exist, if verification does not take place.
   *
   * @group rogo_directory
   */
  public function test_url_verify_file() {
    $url = $this->rogodirectory->url('testfile.txt', false, false);
    $this->assertContains($this->webroot . 'getfile.php?', $url);
    $this->assertContains('type=Mock_rogo_directory', $url);
    $this->assertContains('filename=testfile.txt', $url);
    $this->assertNotContains('forcedownload=1', $url);
  }

  /**
   * Tests that the url method throws an exception for a file that does not exist, if verification takes place.
   *
   * @expectedException file_not_found
   * @group rogo_directory
   */
  public function test_url_verify_file_exception() {
    $url = $this->rogodirectory->url('testfile.txt', false, true);
  }

  /**
   * Tests that the url method generates a valid url when not escaped.
   *
   * @group rogo_directory
   */
  public function test_url_not_escaped() {
    $structre = array(
      'test' => array(
        'testfile.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $url = $this->rogodirectory->url('testfile.txt', false, false, false);
    $this->assertContains($this->webroot . 'getfile.php?', $url);
    $this->assertContains('type=Mock_rogo_directory', $url);
    $this->assertContains('filename=testfile.txt', $url);
    $this->assertNotContains('forcedownload=1', $url);
  }

  /**
   * Tests that the url method generates a valid url when escaped.
   *
   * @group rogo_directory
   */
  public function test_url_escaped() {
    $structre = array(
      'test' => array(
        'testfile.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $url = $this->rogodirectory->url('testfile.txt', false, false, true);
    $this->assertContains(htmlentities($this->webroot . 'getfile.php?', ENT_HTML5), $url);
    $this->assertContains('type&equals;Mock&lowbar;rogo&lowbar;directory', $url);
    $this->assertContains('filename&equals;testfile&period;txt', $url);
    $this->assertContains('&amp;', $url);
    $this->assertNotContains('forcedownload&equals;1', $url);
  }

  /**
   * Tests that the url method generates a valid url when escaped and force download is enabled.
   *
   * @group rogo_directory
   */
  public function test_url_escaped2() {
    $structre = array(
      'test' => array(
        'testfile.txt' => 'test content',
      ),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $url = $this->rogodirectory->url('testfile.txt', true, false, true);
    $this->assertContains(htmlentities($this->webroot . 'getfile.php?', ENT_HTML5), $url);
    $this->assertContains('type&equals;Mock&lowbar;rogo&lowbar;directory', $url);
    $this->assertContains('filename&equals;testfile&period;txt', $url);
    $this->assertContains('&amp;', $url);
    $this->assertContains('forcedownload&equals;1', $url);
  }

  /**
   * Tests that the copy directory method copies files.
   *
   * @group rogo_directory
   */
  public function test_copy_from_default() {
    // This test needs to mock more than just the abstract methods so we need to build our own mock object.
    $methods = array('location', 'base_directory', 'default_base_directory');
    $this->rogodirectory = $this->getMockForAbstractClass('rogo_directory', array(), '', true, true, true, $methods);
    // Set up the configured location.
    $this->rogodirectory->expects($this->any())->method('location')->willReturn($this->config->get('cfg_rogo_data') . '/files/');
    $this->rogodirectory->expects($this->any())->method('base_directory')->willReturn($this->config->get('cfg_rogo_data') . '/');
    // Setup the default location.
    $fixtures = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'rogo_directory' . DIRECTORY_SEPARATOR;
    $this->rogodirectory->expects($this->any())->method('default_base_directory')->willReturn($fixtures);
    // Setup the data directory, with no files in it.
    $structre = array(
      'files' => array(),
    );
    vfsStream::setup(UnitTest::DATA_DIRECTORY, 0777, $structre);
    $this->rogodirectory->copy_from_default();
    // The two fixture files should be copied.
    $this->assertCount(2, vfsStreamWrapper::getRoot()->getChild('files')->getChildren());
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('files')->hasChild('test.gif'));
    $this->assertTrue(vfsStreamWrapper::getRoot()->getChild('files')->hasChild('testfile.txt'));
  }
}
