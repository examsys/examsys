<?php
/**
 *
 * Data container for a Lab entity
 *
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2012 string University of Nottingham
 * @package
 */


class Lab {

  private $id;
  private $name;
  private $campus;
  private $building;
  private $room_no;
  private $timetabling;
  private $it_support;
  private $plagiarism;

  /**
   * @return string $id
   */
  public function &get_id() {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function set_id($id) {
    $this->id = $id;
  }

  /**
   * @return string $name
   */
  public function get_name() {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function set_name($name) {
    $this->name = $name;
  }

  /**
   * @return string $campus
   */
  public function get_campus() {
    return $this->campus;
  }

  /**
   * @param string $campus
   */
  public function set_campus($campus) {
    $this->campus = $campus;
  }

  /**
   * @return string $building
   */
  public function get_building() {
    return $this->building;
  }

  /**
   * @param string $building
   */
  public function set_building($building) {
    $this->building = $building;
  }

  /**
   * @return string $room_no
   */
  public function get_room_no() {
    return $this->room_no;
  }

  /**
   * @param string $room_no
   */
  public function set_room_no($room_no) {
    $this->room_no = $room_no;
  }

  /**
   * @return string $timetabling
   */
  public function get_timetabling() {
    return $this->timetabling;
  }

  /**
   * @param string $timetabling
   */
  public function set_timetabling($timetabling) {
    $this->timetabling = $timetabling;
  }

  /**
   * @return string $it_support
   */
  public function get_it_support() {
    return $this->it_support;
  }

  /**
   * @param string $it_support
   */
  public function set_it_support($it_support) {
    $this->it_support = $it_support;
  }

  /**
   * @return string $plagiarism
   */
  public function get_plagiarism() {
    return $this->plagiarism;
  }

  /**
   * @param string $plagiarism
   */
  public function set_plagiarism($plagiarism) {
    $this->plagiarism = $plagiarism;
  }


}
