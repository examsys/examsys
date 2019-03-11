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

/**
 * CSV export package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 */
namespace export;

/**
 * Abstract export class the base of all export classes.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright (c) 2018, University of Nottingham
 */
abstract class exporter {
  /**
   * The handler for the file
   * @var object
   */
  protected $handler;

  /**
   * The config object
   * @var object
   */
  protected $config;

  /**
   * The constructor
   * @param \file_handler $handler
   */
  public function __construct($handler) {
    $this->config = \Config::get_instance();
    $this->handler = $handler;
  }

  /**
   * Perform the export
   * @param array $data export data
   */
  abstract public function execute($data);
}