<?php

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
 *
 * The enhanced calculation question
 *
 * @author Anthony Brown, Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
class EnhancedCalc extends Question implements questionInterface {

	protected $configObj;
	protected $db;
	public $alluseranswers;

	public function __construct($configObj) {
		$this->configObj = $configObj;
	}

	public function set_settings($data) {
		$this->settings = $data;
	}

	function error_handling($context = null) {
		return error_handling($this);
	}

	/**
	 * Split answer into number and units if applicable
	 * @param  string $input User answer
	 * @return array         Number and unit components of the string
	 */
	function split_numb_from_unit($input) {
		$input = trim($input);

		$this->decode_settings();
		// User selected the units from a ddl
		$pattern = '/-?(?:0|[1-9]\d*)(?:\.\d*)?(?:[eE][+\-]?\d+)?/';
		$out = preg_match($pattern, $input, $matches);

		if (isset($this->useranswer['uansunit']) and $this->settings['show_units']) {
			if (isset($matches[0])) {
				return array($matches[0], $this->useranswer['uansunit']);
			} else {
				return array($input, $this->useranswer['uansunit']);  // No number matched
			}
		}

		if (is_array($matches) and isset($matches[0])) {
			$sz = strlen($matches[0]);
			$units = trim(substr($input, $sz));
			$numb = $matches[0];
			
			return array($numb, $units);
		} else {
			return array($input, '');  // No number matched
		}
	}

	/**
	 * Build an array of formule indexed by their associated units
	 * @param  array $ans Array of possible answers containing a formula and comma separated list of units
	 * @return array      Array of formulae indexed by units string
	 */
	function build_formula_by_units($ans) {
		$formula_by_units = array();
		foreach ($ans as $key => $value) {
			$units = explode(',', $value['units']);
			foreach ($units as $value1) {
				$value1 = trim($value1);
				$formula_by_units[$value1] = $value['formula'];
			}
		}

		return $formula_by_units;
	}

	/**
	 * Check if the user entered units match any that are defined in the possible answers
	 * @param  string $unit Units as entered by the user
	 * @return boolean      True if the units match any defined in the answers
	 */
	function are_units_correct($unit) {
		$this->decode_settings();
		// Create array of units and functions
		$this->settings['answersexp'] = $this->build_formula_by_units($this->settings['answers']);
		if (isset($this->settings['answersexp'][$unit])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Calculate the user's mark for the question.
	 *
	 * Must handle exclusions
	 *
	 * @return integer Status value from marking calculation
	 */
	public function calculate_user_mark() {

			$returnstatus = null;
			if (is_null($this->useranswer)) {
				$this->error = 'No User Answer';
				$this->qmark = 0;

				return Q_MARKING_UNANSWERABLE;
			}
			if (!isset($this->useranswer['vars'])) {
				$this->error = 'No Variables stored';
				$this->qmark = 0;
        
				return Q_MARKING_UNCALC_ANSWER;
			}

			if (!is_array($this->useranswer)) {
				$this->useranswer = json_decode($this->useranswer, true);
			}

			if (isset($this->useranswer['uans'])) {
				$return = $this->split_numb_from_unit($this->useranswer['uans']);
				$this->useranswer['uansunit'] = $return[1];
				$this->useranswer['uansnumb'] = $return[0];
			}

			if (isset($this->useranswer['uansunit'])) {
				$this->useranswer['ans']['guessedunits'] = $this->useranswer['uansunit'];
				// Are the units correct?
				$this->useranswer['status']['units'] = $this->are_units_correct($this->useranswer['uansunit']);
			} else {
				// No units have been sent for some reason so they cannot be correct,
				// it probably means that somehow the variables for the question have been set,
				// but no answer recorded.
				$this->useranswer['status']['units'] = false;
			}

			if ($this->useranswer['status']['units'] === false) {
				// We can't match the units so this question must be wrong! However, we need to have a formula and a unit to calculate the feedback
				// so just use the first one!
				foreach ($this->settings['answersexp'] as $unit => $formula) {
					$this->useranswer['ans']['formula_used'] = $formula;
					$this->useranswer['ans']['units_used'] = $unit;
					break;
				}
			} else {
				// Setup the fomula and units for the calculation
				$this->useranswer['ans']['formula_used'] = $this->settings['answersexp'][$this->useranswer['uansunit']];
				$this->useranswer['ans']['units_used'] = $this->useranswer['uansunit'];
			}

			$enhancedcalcType = $this->configObj->get('enhancedcalc_type');
			if (!is_null($enhancedcalcType)) {
				require_once $enhancedcalcType . '.php';
				$name = 'enhancedcalc_' . $enhancedcalcType;
				$enhancedcalcObj = new $name($this->configObj->getbyref('enhancedcalculation'));
			} else {
				require_once 'Rrserve.php';
				$enhancedcalcObj = new EnhancedCalc_Rrserve($this->configObj->getbyref('enhancedcalculation'));
			}

			if (is_array($this->useranswer['vars'])) {
				foreach ($this->useranswer['vars'] as $key => $variablessplit) {
					if ($variablessplit === 'ERROR') {
						$this->error = "variable $key is ERROR";
						$this->qmark = 0;
						
						return Q_MARKING_UNANSWERABLE;
					}
				}
			}
			// Run calculate through the external interface if errors catch exception and indicate its still unmarked.
			try {

				/*
				 *
				 *  CALCULATE REQURED NUMERIC VALUES
				 *
				 */
				$this->useranswer['cans'] = $enhancedcalcObj->calculate_correct_ans($this->useranswer['vars'], $this->useranswer['ans']['formula_used']);
              } catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
					$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
					$returnstatus = Q_MARKING_UNCALC_ANSWER;
					$this->useranswer['status']['error'] = true;
					$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
					$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
				}

				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}
      
			try {
				if (isset($this->settings['tolerance_full'])) {
					$this->settings['tolerance_full'] = $this->set_blank_to_zero($this->settings['tolerance_full']);
					switch ($this->settings['fulltoltyp']) {
						case "%":
							$res = $enhancedcalcObj->calculate_tolerance_percent($this->useranswer['cans'], $this->settings['tolerance_full']);
							break;
						case "#":
							$res = $enhancedcalcObj->calculate_tolerance_absolute($this->useranswer['cans'], $this->settings['tolerance_full']);
							break;
					}
					$this->useranswer['ans']['tolerance_full'] = $res['tolerance'];
					$this->useranswer['ans']['tolerance_fullans'] = $res['tolerance_ans'];
					$this->useranswer['ans']['tolerance_fullansneg'] = $res['tolerance_ansneg'];
				}
			} catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
					$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
					$returnstatus = Q_MARKING_UNCALC_FULL_TOLLERANCE;
					$this->useranswer['status']['error'] = true;
					$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
					$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
				}

				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}

			try {
				if (isset($this->settings['tolerance_partial'])) {
					$this->settings['tolerance_partial'] = $this->set_blank_to_zero($this->settings['tolerance_partial']);
					switch ($this->settings['parttoltyp']) {
						case "%":
							$res = $enhancedcalcObj->calculate_tolerance_percent($this->useranswer['cans'], $this->settings['tolerance_partial']);
							break;
						case "#":
							$res = $enhancedcalcObj->calculate_tolerance_absolute($this->useranswer['cans'], $this->settings['tolerance_partial']);
							break;
					}
					$this->useranswer['ans']['tolerance_partial'] = $res['tolerance'];
					$this->useranswer['ans']['tolerance_partialans'] = $res['tolerance_ans'];
					$this->useranswer['ans']['tolerance_partialansneg'] = $res['tolerance_ansneg'];
				}
			} catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
					$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
					$returnstatus = Q_MARKING_UNCALC_PARTIAL_TOLLERANCE;
					$this->useranswer['status']['error'] = true;
					$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
					$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
				}

				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}

			/*
			 *
			 * FORMAT CALCULATED ANS
			 *
			 */
			try {
				if ($this->settings['strictdisplay'] === true and isset($this->settings['dp'])) {
						$function = 'format_number_dp';
						$arg = $this->settings['dp'];
						if ($this->settings['strictzeros'] === true) {
								$function = 'format_number_dp_strict_zeros';
						}
				} elseif ($this->settings['strictdisplay'] === true and isset($this->settings['sf'])) {
						$function = 'format_number_sf';
						$arg = $this->settings['sf'];
				} else {
						//round to student precision
						$function = 'format_number_to_precision_of_other_number';
						$arg = $this->useranswer['uansnumb'];
				}

				$this->useranswer['cans'] = $enhancedcalcObj->$function($this->useranswer['cans'], $arg);

				$this->useranswer['ans']['tolerance_full'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_full'], $arg);
				$this->useranswer['ans']['tolerance_fullans'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_fullans'], $arg);
				$this->useranswer['ans']['tolerance_fullansneg'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_fullansneg'], $arg);

				$this->useranswer['ans']['tolerance_partial'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_partial'], $arg);
				$this->useranswer['ans']['tolerance_partialans'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_partialans'], $arg);
				$this->useranswer['ans']['tolerance_partialansneg'] = $enhancedcalcObj->$function($this->useranswer['ans']['tolerance_partialansneg'], $arg);
			} catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
						$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
						$returnstatus = Q_MARKING_UNCALC_FORMAT;
						$this->useranswer['status']['error'] = true;
						$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
						$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
				}

				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}

			/*
			 *
			 * MARKING
			 *
			 */
			if (!isset($this->useranswer['uansnumb']) or (isset($this->useranswer['uansnumb']) and trim($this->useranswer['uansnumb']) == '')) {
				// Not answered
				$this->qmark = 0;
				$returnstatus = Q_MARKING_NOTANS;
				$this->useranswer['status']['overall'] = $returnstatus;
				
				return $returnstatus;
			}

			if ($this->useranswer['status']['units'] === false) {
				// We can't mach the units so this question must be wrong!
				$this->qmark = $this->settings['marks_incorrect'];
				$this->useranswer['status']['exact'] = false;
				$returnstatus = Q_MARKING_WRONG;
				$this->useranswer['status']['overall'] = $returnstatus;
				
				return $returnstatus;
			}

			try {
				$this->useranswer['status']['exact'] = $enhancedcalcObj->is_useranswer_correct($this->useranswer['uansnumb'], $this->useranswer['cans'], ($this->settings['strictdisplay'] !== true));
			} catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
					$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
					$returnstatus = Q_MARKING_UNCALC_USER_ANSWER;
					$this->useranswer['status']['error'] = true;
					$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
					$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
					// If this happens the user's answer caused an error in the checker.
					// This almost certainly means it is not a number, so we should give them
					// the incorrect score.
					$this->qmark = $this->settings['marks_incorrect'];
				}

				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}

			try {
				// Calculate distance from correct if needed
				if ($this->useranswer['status']['exact'] === false) {
					$this->useranswer['cans_dist'] = $enhancedcalcObj->distance_from_correct_answer($this->useranswer['uansnumb'], $this->useranswer['cans']);
				} else {
					$this->useranswer['cans_dist'] = '0';
				}
			} catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
					$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
					$returnstatus = Q_MARKING_UNCALC_DIST_FROM_ANSWER;
					$this->useranswer['status']['error'] = true;
					$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
					$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
				}

				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}

			if ($this->useranswer['status']['exact'] === false) {
				try {
					$this->useranswer['status']['tolerance_full'] = $enhancedcalcObj->is_useranswer_within_tolerance(
									$this->useranswer['uansnumb'], $this->useranswer['ans']['tolerance_fullansneg'], $this->useranswer['ans']['tolerance_fullans']
					);
				} catch (Exception $e) {
					//TODO: catch different errors "no connection", "unable to evaluate"
					if (stripos($e->getMessage(), 'connect') !== false) {
							$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
					} else {
							$returnstatus = Q_MARKING_UNCALC_WITHIN_FULL_TOLERANCE;
							$this->useranswer['status']['error'] = true;
							$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
							$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
					}
					$this->useranswer['status']['overall'] = $returnstatus;

					return $returnstatus;
				}

				if ($this->useranswer['status']['tolerance_full'] === false) {
					try {
						$this->useranswer['status']['tolerance_partial'] = $enhancedcalcObj->is_useranswer_within_tolerance(
										$this->useranswer['uansnumb'], $this->useranswer['ans']['tolerance_partialansneg'], $this->useranswer['ans']['tolerance_partialans']
						);
					} catch (Exception $e) {
						//TODO: catch different errors "no connection", "unable to evaluate"
						if (stripos($e->getMessage(), 'connect') !== false) {
							$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
						} else {
							$returnstatus = Q_MARKING_UNCALC_WITHIN_PARTIAL_TOLERANCE;
							$this->useranswer['status']['error'] = true;
							$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
							$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
						}
						$this->useranswer['status']['overall'] = $returnstatus;

						return $returnstatus;
					}
				}
			} else {
				$this->useranswer['status']['tolerance_partial'] = true;
				$this->useranswer['status']['tolerance_full'] = true;
			}

			try {
				// Strict dp marking
				if ($this->is_strict_dp_enabled()) {

					if ($this->is_strict_dp_strictzeros_enabled()) {
						$this->useranswer['status']['strictdp'] = $enhancedcalcObj->is_useranswer_correct_decimal_places_strictzeros($this->useranswer['uansnumb'], $this->settings['dp']);
					} else {
						$this->useranswer['status']['strictdp'] = $enhancedcalcObj->is_useranswer_correct_decimal_places($this->useranswer['uansnumb'], $this->settings['dp']);
					}
					if ($this->useranswer['status']['strictdp'] === false) {
						$this->qmark = $this->settings['marks_incorrect'];
						$returnstatus = Q_MARKING_WRONG;
						$this->useranswer['status']['overall'] = $returnstatus;
						return $returnstatus;
					}
				}
			} catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
					$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
					$returnstatus = Q_MARKING_UNCALC_STRICT_DP_CHECK;
					$this->useranswer['status']['error'] = true;
					$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
					$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage();
				}

				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}

			try {
				// Sheck for strict sf
				if ($this->is_strict_sf_enabled()) {
					$this->useranswer['status']['strictsf'] = $enhancedcalcObj->is_useranswer_within_significant_figures($this->useranswer['uansnumb'], $this->settings['sf']);
					if ($this->useranswer['status']['strictsf'] === false) {
						$this->qmark = $this->settings['marks_incorrect'];
						$returnstatus = Q_MARKING_WRONG;
						$this->useranswer['status']['overall'] = $returnstatus;
						
						return $returnstatus;
					}
				}

				// Assume its wrong wrong !!
				$returnstatus = Q_MARKING_WRONG;
				$this->qmark = $this->settings['marks_incorrect'];

				// Part tolerance range
				if ($this->is_user_ans_within_partial_tolerance()) {
					$this->qmark = $this->settings['marks_partial'];
					$returnstatus = Q_MARKING_PART_TOL;
				}

				// Full tolerance range
				if ($this->is_user_ans_within_fullmark_tolerance()) {
					$this->qmark = $this->settings['marks_correct'];
					$returnstatus = Q_MARKING_FULL_TOL;
				}

				// Exact answer
				if ($this->is_user_ans_correct()) {
					$this->qmark = $this->settings['marks_correct'];
					$returnstatus = Q_MARKING_EXACT;
				}

				// Remove marks for incorrect unit
				if ((isset($this->settings['unit_marks']) and !($this->settings['unit_marks'] == 0 or $this->settings['unit_marks'] == 'invalidate')) and $this->useranswer['status']['units'] !== true) {
					$this->qmark = $this->qmark - $this->settings['unit_marks'];
					$returnstatus = Q_MARKING_PART_UNITS_WRONG;
				}

				$this->useranswer['status']['overall'] = $returnstatus;
			} catch (Exception $e) {
				//TODO: catch different errors "no connection", "unable to evaluate"
				if (stripos($e->getMessage(), 'connect') !== false) {
					$returnstatus = Q_MARKING_UNMARKED;   // Set to unmarked as there is no connection to R serve.
				} else {
					$returnstatus = Q_MARKING_ERROR;
					$this->useranswer['status']['error'] = true;
					$this->useranswer['ans']['error'] = $enhancedcalcObj->get_error();
					$this->useranswer['status']['e'] = $e->getCode() . " - " . $e->getMessage() . " - " . $e->getTraceAsString();
				}
				$this->useranswer['status']['overall'] = $returnstatus;

				return $returnstatus;
			}

			return $returnstatus;
	}

	/**
	 * Convert the user answer array to a JSON encoded string
	 * @return string Answer data in JSON format
	 */
	public function useranswer_to_string() {
		return json_encode($this->useranswer);
	}

	/**
	 * Process the POST data for the user's answer into JSON
	 * @param  array  $postdata HTML POST data for theuser's answer
	 * @param  [type] $session  [description]
	 * @return string           JSON encoded answer data
	 */
	static public function process_user_answer(&$postdata, &$session) {
		$data = $session;
    
		foreach ($postdata as $key => $value) {
			$data[$key] = $value;
		}

		$return = json_encode($data);

		return $return;
	}

	/**
	 * Return the maximum number of marks for the question
	 * @return integer Marks available for correct answers
	 */
	public function calculate_question_mark() {
		$this->decode_settings();

		return $this->settings['marks_correct'];
	}

	/**
	 * Calculate the Random Mark for this question
	 * @return integer  Expected marks if answeing the question by guessing
	 */
	public function calculate_random_mark() {
		return 0;				// The chances of getting a calculation question correct by luck is extremely small - return zero.
	}

	/**
	 * Is this question excluded
	 * @return boolean true if question has been exluded due to poor performance
	 */
	function is_excluded() {
		return (isset($this->excluded{0}) and $this->excluded{0} == 1);
	}

	/**
	 * Is the user's answer correct
	 * @return boolean True if answer is correct
	 */
	function is_user_ans_correct() {
		return (isset($this->useranswer['status']['exact']) and $this->useranswer['status']['exact'] === true);
	}

	/**
	 * Is the user's answer within tolerance for full marks
	 * @return boolean True if answer is within tolerance for full marks
	 */
	function is_user_ans_within_fullmark_tolerance() {
		return (isset($this->useranswer['status']['tolerance_full']) and $this->useranswer['status']['tolerance_full'] === true);
	}

	/**
	 * Is the user's answer within tolerance for full marks
	 * @return boolean True if answer is within tolerance for partial marks
	 */
	function is_user_ans_within_partial_tolerance() {
		return (isset($this->useranswer['status']['tolerance_partial']) and $this->useranswer['status']['tolerance_partial'] === true);
	}

	/**
	 * Did the user enter correct units
	 * @return boolean True if units were correct
	 */
	function is_user_ans_units_correct() {
		return $this->useranswer['status']['units'];
	}

	/**
	 * Is the question set to require answers strictly to the defined number of decimal places
	 * @return boolean True if using strict decimal places
	 */
	function is_strict_dp_enabled() {
		return (isset($this->settings['strictdisplay']) and $this->settings['strictdisplay'] === true and isset($this->settings['dp']));
	}

	/**
	 * Does strict decimal places include any trailing zeros
	 * @return boolean True if trailing zeros are significant when determining strict decimal places
	 */
	function is_strict_dp_strictzeros_enabled() {
		return (isset($this->settings['strictzeros']) and $this->settings['strictzeros'] === true);
	}

	/**
	 * Is the question set to require answers strictly to the defined number of significant figures
	 * @return boolean True if using strict significant figures
	 */
	function is_strict_sf_enabled() {
		return (isset($this->settings['strictdisplay']) and $this->settings['strictdisplay'] === true) and isset($this->settings['sf']);
	}

	/*
	 * return the passed value or 0 if the value is an empty string 
	 */

	private function set_blank_to_zero($val) {
		return ($val === '' ? 0 : $val);
	}

	/*
	 * Display the question
	 *
	 * The Paper handles question numbering this function renders the inner part of the question
	 * we need at least 2 renders one for the exam script (start.php) one for formative feedback on (finish.php)
	 */

	public function render() {
			
	}

	/**
	 * Render the querstion as required for displaying results and feedback to the user
	 * @param  array  $extra [description]
	 */
	public function render_feedback($extra = array()) {
            global $string;

            // Make sure data is arrays not encoded
            if (!is_array($this->useranswer)) {
                $this->useranswer = json_decode($this->useranswer, true);
            }
            if (!is_array($this->settings)) {
                $this->settings = json_decode($this->settings, true);
            }

            if (isset($this->useranswer['vars'])) {
                $varname = array_keys($this->useranswer['vars']);
                $varvalue = array_values($this->useranswer['vars']);
            } else {
                $varname = array('$A', '$B', '$C', '$D', '$E', '$F', '$G', '$H', '$I', '$J', '$K');
                $varvalue = array_fill(0, 11, '<span class="var_error"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="!">&nbsp;ERROR</span>');
            }

            $leadin = str_ireplace($varname, $varvalue, $this->leadin);

            // Deal with the failed variables

            if ($this->scenario != '') {
                echo "<p>" . $this->scenario . "</p>\n";
            }
            if ($this->q_media != '') {
                echo "<p align=\"center\">" . display_media($this->q_media, $this->q_media_width, $this->q_media_height, '') . "</p>\n";
            }

            echo_content($leadin);

            if (!isset($this->useranswer['uans']) or $this->useranswer['uans'] == '') {
                if ($extra['hide_if_unanswered'] == 1) {
                    $extra['tmp_display_correct_answer'] = 0;
                    $extra['tmp_display_students_response'] = '0';
                    $extra['tmp_display_feedback'] = '0';
                    $extra['tmp_display_question_mark'] = '0';
                }
            }

            $saved_response = '';
            $saved_response_clean = '';
            if (isset($this->useranswer['uans'])) {
                $saved_response = $this->useranswer['uans'];
                $saved_response_clean = preg_replace('([^0-9\.\-])', '', $saved_response);
            }
            $part_id = 1;

            $tmp_fback = $this->correct_fback;

            echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\"><tr>";
            if ($extra['tmp_display_correct_answer'] == '1') {
                echo '<td>';
                if (isset($this->std[0])) {
                    echo display_std($this->std[0]);
                }
                echo '</td>';
            } else {
                echo '<td></td>';
            }
            $marked = true;
            if ($saved_response_clean == '') {
                echo '<td>';
                if ($extra['tmp_exclude'] == '1') {
                    echo '<span class="exclude">';
                }
                echo display_response($extra['tmp_display_students_response'], 'blank') . "<input type=\"text\" style=\"color:#808080; text-align:right\" name=\"q'" . $extra['question'] . "'\" size=\"10\" value=\"" . $string['unanswered'] . "\" />";
            } else {
                echo '<td>';
                if ($extra['tmp_exclude'] == '1') {
                    echo '<span class="exclude">';
                }

                if (isset($this->useranswer['status']['overall']) and ($this->useranswer['status']['overall'] == Q_MARKING_EXACT or $this->useranswer['status']['overall'] == Q_MARKING_FULL_TOL)) {
                    echo display_response($extra['tmp_display_students_response'], 'tick');
                } elseif (isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_PART_TOL) {
                    echo display_response($extra['tmp_display_students_response'], 'half');
                } elseif (isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_WRONG) {
                    echo display_response($extra['tmp_display_students_response'], 'cross');
                } else {
                    echo display_response($extra['tmp_display_students_response'], 'unmarked');
                    $marked = false;
                }
                if ($marked) {
                    echo '<input type="text" style="text-align:right" name="q' . $extra['question'] . '" size="10" value="' . htmlspecialchars($this->useranswer['uansnumb']) . ' ' . $this->useranswer['uansunit'] . '" />';
                }
            }

            $display_units = '';

            if ($marked) {
                if ($this->useranswer['ans']['units_used'] != '') {
                    $display_units = ' ' . $this->useranswer['ans']['units_used'];
                }
            }
            if ($extra['tmp_display_correct_answer'] == '1') {
                if (!isset($this->useranswer['status'])) {
                    echo ' <strong><span class="err">' . $string['unmarked'] . '</span></strong>';
                } elseif (!isset($this->useranswer['cans'])) {
                    echo ' <strong><span class="err">' . $string['EnhancedCalcCorrectError'] . '</span></strong>';
                } else {
                    echo ' <strong>' . $this->useranswer['cans'] . $display_units . '</strong>';
                }
            } else {
                echo ' ';
            }

            if (isset($this->useranswer['cans'])) {
                if (isset($this->useranswer['status']['overall']) and ($this->useranswer['status']['overall'] == Q_MARKING_FULL_TOL)) {
                    echo ' ' . $string['withatoleranceof'] . ' ' . $this->settings['tolerance_full'] . str_replace('#', '', $this->settings['fulltoltyp']);
                    echo ' (' . $this->useranswer['ans']['tolerance_fullansneg'] . $display_units . ' - ' . $this->useranswer['ans']['tolerance_fullans'] . $display_units . ')';
                }
                if (isset($this->useranswer['status']['overall']) and $this->useranswer['status']['overall'] == Q_MARKING_PART_TOL) {
                    echo ' ' . $string['withatoleranceof'] . ' ' . $this->settings['tolerance_partial'] . str_replace('#', '', $this->settings['parttoltyp']);
                    echo ' (' . $this->useranswer['ans']['tolerance_partialansneg'] . $display_units . ' - ' . $this->useranswer['ans']['tolerance_partialans'] . $display_units . ')';
                }
            }

            if ($extra['tmp_exclude'] == '1') {
                echo '</span>';
            }
            echo "</td></tr>\n</table>\n";
            if ($tmp_fback != '' and $extra['tmp_display_feedback'] == '1') {
                foreach ($varname as $individual_varname) {
                    $tmp_fback = str_replace($individual_varname, $this->useranswer['vars'][$individual_varname], $tmp_fback);
                }
                echo "<br /><div class=\"fback\">" . nl2br($tmp_fback) . "</div>\n";
            }
	}

	/**
	 * Load the answers for all users
	 * @param  [type] $all_user_answers [description]
	 */
	function load_all_user_answers(&$all_user_answers) {
		$this->alluseranswers = $all_user_answers;
	}

	/**
	 * Substitute variable placeholders with the calculated value
	 * @param  string $inputVal     The variable definition
	 * @param  [type] $user_answers [description]
	 * @return [type]               [description]
	 */
	function variable_substitution($inputVal, $user_answers) {

            //Question reference
            if ($this->is_linked_ans($inputVal)) {
                $uansarray = array();
                // 1. List answer and associated question
                $find_qid = $this->parse_linked_ans($inputVal);
                // 2. Error if user answers not set.
                if (!is_array($user_answers)) {
                    $user_answers = array();
                    $inputVal = 'ERROR';
                }
                // 3. Loop though user answers.
                foreach ($user_answers as $screen => $answers) {
                    // Check user answer for question exists.
                    if (isset($answers[$find_qid])) {
                        try {
                            // Decode if not already an array.
                            if (!is_array($answers[$find_qid])) {
                                $uansarray = json_decode($answers[$find_qid], true);
                            } else {
                                $uansarray = $answers[$find_qid];
                            }
                        } catch (exception $e) {
                            return 'ERROR';
                        }
                        break;
                    }
                }
                // 4. Error is user answer is empty.
                if (!isset($uansarray['uans']) or $uansarray['uans'] == '') {
                    return 'ERROR';
                }
                // 5. Return the number only.
                $return = $this->split_numb_from_unit($uansarray['uans']);
                $inputVal = $return[0];
            }

            // Variable reference
            if ($this->is_linked_question_var($inputVal)) {
                // 1. Get variable and associated question.
                list($find_var, $find_qid) = $this->parse_linked_question_var($inputVal);
                // 2. Error if user answers not set.
                if (!is_array($user_answers)) {
                    $user_answers = array();
                    $inputVal = 'ERROR';
                }
                // 3. Loop though user answers.
                foreach ($user_answers as $screen => $answers) {
                    // Check user answer for question exists or error.
                    if (isset($answers[$find_qid])) {
                        // Decode if not already an array.
                        if (!is_array($answers[$find_qid])) {
                            $variables = json_decode($answers[$find_qid], true);
                        } else {
                            $variables = $answers[$find_qid];
                        }
                        // Set input value to variable or error.
                        if (isset($variables['vars'][$find_var])) {
                            $inputVal = $variables['vars'][$find_var];
                            break;
                        } else {
                            $inputVal = 'ERROR';
                        }
                    } else {
                        $inputVal = 'ERROR';
                    }
                }
            }

            // Substitue values.
            if ($this->is_compound_question_var($inputVal) or (!is_numeric($inputVal) and $inputVal != 'ERROR' and $inputVal !== '')) {
                $inputVal = $this->substitute_and_eval_vars($this->useranswer['vars'], $inputVal);
                if (!is_numeric($inputVal)) {
                    $inputVal = 'ERROR';
                }
            }

            return $inputVal;
	}

	/**
	 * test to see if a var is linked to a previous answer
	 * @param  string var min or max
	 * @return bool
	 */
	public function is_linked_ans($varval) {
		if (substr($varval, 0, 3) == 'ans') {
			return true;
		}
		return false;
	}

	/**
	 * Split the q_id from a linked answer 
	 * @param type $varval
	 * @return type rogo q_id
	 */
	public function parse_linked_ans($varval) {
		return intval(substr($varval, 3)); //qid
	}

	/**
	 * test to see if a var is linked to a previous question
	 * @param  string  var min or max
	 * @return bool
	 */
	public function is_linked_question_var($varval) {
		if (substr($varval, 0, 3) == 'var') {
			return true;
		}
		return false;
	}
	
	/**
	 * test to see if a var is built from previous vars
	 * @param  string  var min or max
	 * @return bool
	 */
	public function is_compound_question_var($varval) {
		if (stripos($varval, '$') !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * replace $A,$B,$C .... in a string and evluate using php eval
	 * N.B ONLY Used to calculate compound question varables only in in Rserve mode
	 * 
	 * @param  array $vars array('$VARNAME'=>VALUE)
	 * @param string $formula sting in the format "($A+$B)/$C"
	 * @return bool
	 */
	public function substitute_and_eval_vars($vars, $formula) {
		$varname = array_keys($vars);
		$varvalue = array_values($vars);
		$vars_subed = str_replace($varname, $varvalue, $formula);
    
		return @eval( "return (string)(" . $vars_subed . ");");
	}
	
	/**
	 * Split the q_id and varname from a linked question var 
	 * @param type $varval
	 * @return type array(varname, q_id)
	 */
	public function parse_linked_question_var($varval) {
		$varname = substr($varval, 3, 2);
		$qid = intval(substr($varval, 5));
		
		return array($varname, $qid);
	}

	/**
	 * Render the question in the format required when taking the paper
	 * @param  array  $extra [description]
	 */
	public function generate_variables() {

		if (!isset($this->useranswer['vars']) or !is_array($this->useranswer['vars'])) {
			// Create an empty array to hold the generated variables
			$this->useranswer['vars'] = array();
		}

		// Check to see if variables have been previously generated if not put them in an array to be generated
		foreach ($this->settings['vars'] as $key => $value) {
			if ((!isset($this->useranswer['vars'][$key]) or $this->useranswer['vars'][$key] === 'ERROR') and !$this->is_linked_ans($value['min'])) {
				$min = $this->variable_substitution($value['min'], $this->alluseranswers);
				if ($value['max'] == '') {
					//value for max not set force it to min to generate a fixed value.
					$value['max'] = $value['min'];
				}
				$max = $this->variable_substitution($value['max'], $this->alluseranswers);
				// Temporary fix ROGO-1468 until we introduce proper localisation we need to be able to handle ',' decimal places.
				$inc = $this->variable_substitution(str_replace(',', '.', $value['inc']), $this->alluseranswers);
				$dec = (int)$value['dec'];

				$this->useranswer['vars'][$key] = MathsUtils::gen_random_no($min, $max, $inc, $dec);
			}

			// Pull in the last userans every time
			if ($this->is_linked_ans($value['min'])) {
				$this->useranswer['vars'][$key] = $this->variable_substitution($value['min'], $this->alluseranswers);
			}
		}
		// Update the session
		$_SESSION['qid'][$this->id]['vars'] = $this->useranswer['vars'];
	}

	public function replace_leadin($reviewers = false) {

		if ($reviewers === false) {
			$leadin = $this->replace_vars($this->leadin);
		} else {
			$leadin = $this->leadin;
			foreach ($this->useranswer['vars'] as $key => $value) {
				$leadin = str_replace($key, '<span class="var">' . $key . '</span><span class="value">' . $value . '</span>', $leadin);
			}
		}

		return $leadin;
	}

	public function replace_vars($string) {
		$varname = array_keys($this->useranswer['vars']);
		$varvalue = array_values($this->useranswer['vars']);
		$string = str_ireplace($varname, $varvalue, $string);

		return $string;
	}

	public function decode_settings() {
		if (!is_array($this->settings)) {
			$this->settings = json_decode($this->settings, true);
		}
	}

	public function render_paper($extra = array()) {
		global $string;

		// Display question on paper
		$screen_pre_submitted = null;
		if (isset($extra['screen_pre_submitted'])) {
			$screen_pre_submitted = $extra['screen_pre_submitted'];
		}

		// Make sure data is arrays not encoded
		if (!is_array($this->useranswer)) {
			$this->useranswer = json_decode($this->useranswer, true);
		}
		$this->decode_settings();

		// Create array of units and functions
		if ((isset($this->settings['answersexp']) and !is_array($this->settings['answersexp'])) or (!isset($this->settings['answersexp']))) {
			foreach ($this->settings['answers'] as $key => $value) {
				$units = explode(',', $value['units']);
				foreach ($units as $value1) {
					$value1 = trim($value1);
					$this->settings['answersexp'][$value1] = $value['formula'];
				}
			}
		}

		$calculatevars = array();

		$this->generate_variables();

		$varname = array_keys($this->useranswer['vars']);
		$varvalue = array_values($this->useranswer['vars']);

		if (isset($extra['reviewers']) and $extra['reviewers']) {
			$leadin = $this->replace_leadin(true);
		} else {
			$leadin = $this->replace_leadin(false);
		}

		$dispunits = '';
		if ($this->settings['show_units'] === true) {
			if (count($this->settings['answersexp']) > 1) {
				// Make drop down of units
				$dispunits = "&nbsp;&nbsp;<select name='qid[" . $this->id . "][uansunit]'>";
				foreach ($this->settings['answersexp'] as $key => $value) {
					$dispunits = $dispunits . "<option value='$key'>$key</option>";
				}
				$dispunits = $dispunits . '</select>';
			} else {
				$dispunits = array_keys($this->settings['answersexp']);
				$dispunits = "&nbsp;&nbsp;" . $dispunits[0] . "<input type=\"hidden\" name=\"qid[" . $this->id . "][uansunit]\" value=\"" . $dispunits[0] . "\" />";
			}
		}

		if (isset($extra['reviewers']) and $extra['reviewers']) {    // Display additional information for reviewers
			echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"padding:10px; border: 2px solid #FCE699; background-color:#FFFFEE; width:700px\">\n";
			echo "<tr><td colspan=\"4\">" . $string['notvisible'] . "</td></tr>";
			echo "<tr><td colspan=\"4\" style=\"text-align:justify\">" . $string['reviewermsg'] . "</td></tr>";
			echo "<tr><td colspan=\"4\">&nbsp;</td></tr>";
			echo "<tr style=\"font-weight:bold\"><td style=\"width:80px; border-bottom: 1px solid #FCE699\">{$string['variable']}</td><td style=\"width:80px; border-bottom: 1px solid #FCE699\">" . $string['generated'] . "</td><td style=\"width:80px; border-bottom: 1px solid #FCE699\">" . $string['min'] . "</td><td style=\"width:460px; border-bottom: 1px solid #FCE699\">" . $string['max'] . "</td></tr>\n";

			foreach ($this->settings['vars'] as $key => $value) {
				echo "<tr><td>" . $key . "</td><td>" . $this->useranswer['vars'][$key] . "</td><td>" . $value['min'] . "</td><td>" . $value['max'] . "</td></tr>\n";
			}
			$formula_no = 1;
			foreach ($this->settings['answers'] as $answer) {
				echo "<tr><td>" . $string['formula'] . " $formula_no</td><td colspan=\"2\">" . $answer['formula'] . "</td><td>units: " . $answer['units'] . "</td></tr>\n";
				$formula_no++;
			}
			if (strlen($this->settings['tolerance_full']) > 0) {
				echo "<tr><td colspan=\"3\">{$string['tolerancefull']}</td><td>" . $this->settings['tolerance_full'];
				if ($this->settings['fulltoltyp'] == '%') {
					echo '%';
				}
				echo "</td></tr>\n";
			}
			if (strlen($this->settings['tolerance_partial']) > 0) {
				echo "<tr><td colspan=\"3\">{$string['tolerancepartial']}</td><td>" . $this->settings['tolerance_partial'];
				if ($this->settings['parttoltyp'] == '%') {
					echo '%';
				}
				echo "</td></tr>\n";
			}
      echo "<tr><td colspan=\"4\"><input type=\"button\" class=\"reveal\" value=\"" . $string['togglevariables'] . "\" /></td></tr>\n";
			echo "</table>\n<br />";

			$real_answer = $this->get_real_answer();
			$this->add_to_useranswer('uans', $real_answer);  // Get the real answer and override
		}

		if ($this->scenario != '') {
			echo "<p>" . $this->scenario . "</p>\n";
		}
		if ($this->q_media != '') {
			echo "<p align=\"center\">" . display_media($this->q_media, $this->q_media_width, $this->q_media_height, '') . "</p>\n";
		}

		$marking_precision_feedback = '';
		if ($this->is_strict_dp_enabled()) {
			$marking_precision_feedback = " <span class=\"calc_fb\">(" . $string['answer_to'] . " " . $this->settings['dp'] . " " . $string['decimal_places'] . ")</span>";
		} else if ($this->is_strict_sf_enabled()) {
			$marking_precision_feedback = " <span class=\"calc_fb\">(" . $string['answer_to'] . " " . $this->settings['sf'] . " " . $string['significant_figures'] . ")</span>";
		}
		
		echo $leadin;
		
		if (in_array('ERROR', $varvalue, true)) {
			echo "<p><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" disabled=\"disabled\" />" . $dispunits . $marking_precision_feedback . "</p>\n";
		} else {
			if (isset($this->useranswer['uans']) and $this->useranswer['uans'] == '') {
				echo "<div><input type=\"text\" style=\"text-align:right\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" class=\"unans ecalc-answer\" />" . $dispunits . $marking_precision_feedback . "</div>\n";
			} else {
				if ((isset($this->useranswer['uans']) and $this->useranswer['uans'] != '')) { // Or $screen_pre_submitted == 0
					$ans = $this->useranswer['uans'];

					echo "<div><input type=\"text\" style=\"text-align:right\" id=\"q{$extra['num_on_screen']}\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"" . $ans . "\" class=\"ecalc-answer\" />" . $dispunits . $marking_precision_feedback . "</div>\n";
				} else {
					echo "<div><input type=\"text\" style=\"text-align:right\" class=\"ecalc-answer\" id=\"q{$extra['num_on_screen']}\" name=\"qid[" . $this->id . "][uans]\" size=\"10\" value=\"\" />" . $dispunits . $marking_precision_feedback . "</div>\n";
					$unanswered = true;
				}
			}
		}

		$marks = $this->settings['marks_correct'];
	}

	/**
	 * Get the veriables as defined in the question
	 * @return array Array of defined variables indexed by the label (e.g. $A)
	 */
	public function get_question_vars() {
		$this->decode_settings();
		
		return (isset($this->settings['vars'])) ? $this->settings['vars'] : array();
	}

	/**
	 * Set the veriables as defined in the question
	 */
	public function set_question_vars($vars) {
		$this->decode_settings();
		$this->settings['vars'] = $vars;
	}

	public function get_user_vars() {
		$this->decode_settings();
		
		return $this->useranswer['vars'];
	}

	/**
	 * Get the marks as defined for the question
	 * @return array Array of marks
	 */
	public function get_question_marks() {
		$this->decode_settings();
		$marks_full = isset($this->settings['marks_correct']) ? $this->settings['marks_correct'] : false;
		$marks_partial = isset($this->settings['marks_partial']) ? $this->settings['marks_partial'] : false;
		$marks_incorrect = isset($this->settings['marks_incorrect']) ? $this->settings['marks_incorrect'] : 0;

		if ($marks_full !== false and $marks_partial !== false) {
			return array('correct' => $marks_full, 'partial' => $marks_partial, 'incorrect' => $marks_incorrect);
		} else {
			return false;
		}
	}

	/**
	 * Get whether the question is set to disply uints to the user
	 * @return boolean Whether to show units for the question
	 */
	public function get_show_units() {
		return (isset($this->settings['show_units'])) ? ($this->settings['show_units'] == true) : false;
	}

	/**
	 * Get the full correct answer for the question
	 * @return string Answer including units if applicable
	 */
	public function get_real_answer() {
    global $string;
		$this->decode_settings();
		$units = $this->settings['answers'][0]['units'];

		$this->add_to_useranswer('uans', "1 $units");   // Set a bogus answer before marking.
		$this->calculate_user_mark();

		if ($this->settings['show_units'] == true and isset($this->useranswer['cans'])) {
			return $this->useranswer['cans'];
		} else if (isset($this->useranswer['cans'])) {
			return $this->useranswer['cans'] . ' ' . $units;
    } else {
      return 'error';
    }
	}

	/**
	 * Return the answer as entered by the user
	 * @return string The user's raw answer
	 */
	public function get_user_answer_raw() {
		return (isset($this->useranswer['uans'])) ? $this->useranswer['uans'] : '';
	}

	public function get_user_answer_full() {
		$ret = '';
		if (isset($this->useranswer['uansnumb'])) {
			$ret = $this->useranswer['uansnumb'] . ' ' . $this->useranswer['uansunit'];
		}
		return $ret;
	}

	/**
	 * Get the units selected by a user if units have been displayed
	 * @return string Units selected by the user
	 */
	public function get_user_answer_units() {
		return (isset($this->useranswer['uansunit'])) ? $this->useranswer['uansunit'] : '';
	}

	/**
	 * Get the usits used when selecting the formula to match the user's answer
	 * @return string Units used to mark the answer
	 */
	public function get_user_answer_units_used() {
		return (isset($this->useranswer['ans']['units_used'])) ? $this->useranswer['ans']['units_used'] : '';
	}

	/**
	 * Return the 'distance' of the user's answer from the correct answer as a percentage of the correct answer
	 * @return float Distance from the correct answer
	 */
	public function get_answer_distance() {

		if (!isset($this->useranswer['cans_dist'])) {
			$enhancedcalcType = $this->configObj->get('enhancedcalc_type');
			if (!is_null($enhancedcalcType)) {
				require_once $enhancedcalcType . '.php';
				$name = 'enhancedcalc_' . $enhancedcalcType;
				$enhancedcalcObj = new $name($this->configObj->getbyref($enhancedcalcType));
			} else {
				require_once 'Rrserve.php';
				$enhancedcalcObj = new EnhancedCalc_Rrserve($this->configObj->getbyref('enhancedcalculation'));
			}

			if ((isset($this->useranswer['status']['exact']) and $this->useranswer['status']['exact'] === false) or !isset($this->useranswer['status']['exact'])) {
				$this->useranswer['cans_dist'] = $enhancedcalcObj->distance_from_correct_answer($this->useranswer['uansnumb'], $this->useranswer['cans']);
			} else {
				$this->useranswer['cans_dist'] = '0';
			}
		}

		$data = false;

		if (isset($this->useranswer['cans_dist']) and $this->useranswer['cans_dist'] !== 'ERROR') {
			$data = $this->useranswer['cans_dist'];
		}

		return $data;
	}

	/**
	 * Is the question negatively marked?
	 * @return boolean True if incorrect mark is less than 0
	 */
	public function is_negative_marked() {
		$this->decode_settings();

		return $this->settings['marks_incorrect'] < 0;
	}

	public function get_uans_data() {
		if (isset($this->useranswer['ans'])) {
			return $this->useranswer['ans'];
		}

		return null;
	}

}
?>
