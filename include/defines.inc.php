<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defined variables should be placed in this file.
 *
 * It will be required by load_config.php, so they should be
 * available on most ExamSys pages.
 *
 * Only define statements should be in this file.
 *
 * @author Neill Magill
 * @version 1.0
 * @copyright Copyright (c) 2015 The University of Nottingham
 */

// The defined values originally in /classes/authentication.class.php

define('ROGO_AUTH_OBJ_FAILED', 0);
define('ROGO_AUTH_OBJ_SUCCESS', 1);
define('ROGO_AUTH_OBJ_LOOKUPONLY', 2);

// End of defined values originally in /classes/authentication.class.php

// The defined values originally in /classes/question.class.php

define('QUESTION_ERROR', -1);

/** Student answer is an exact match. */
define('Q_MARKING_EXACT', 1);

/** Student answer is within full marks tollerance. */
define('Q_MARKING_FULL_TOL', 2);

/** Student answer is within partial marks tollerance. */
define('Q_MARKING_PART_TOL', 3);

/** Student answer has incorrect units. */
define('Q_MARKING_PART_UNITS_WRONG', 4);

/** Student is marked as wrong. */
define('Q_MARKING_WRONG', 0);

/** Student answer is unmarked. */
define('Q_MARKING_UNMARKED', -1);

/** Student has left question unanswered. */
define('Q_MARKING_NOTANS', -2);

/** Unspecified marking error. */
define('Q_MARKING_ERROR', -3);

/** It is imposible to answer the question (e.g. previous linked question not answered) */
define('Q_MARKING_UNANSWERABLE', -4);

// Error section

/** Error calculating what the correct answer should be. */
define('Q_MARKING_UNCALC_ANSWER', -5);

/** Error determining full tollerance figure. */
define('Q_MARKING_UNCALC_FULL_TOLLERANCE', -6);

/** Error determining partial tollerance figure. */
define('Q_MARKING_UNCALC_PARTIAL_TOLLERANCE', -7);

/** Error with the formatting (dp and sf) */
define('Q_MARKING_UNCALC_FORMAT', -8);

/** Error determining if the user answer is correct. */
define('Q_MARKING_UNCALC_USER_ANSWER', -9);

/** Error calculating the distance from the correct answer. */
define('Q_MARKING_UNCALC_DIST_FROM_ANSWER', -10);

/** Error checking if the answer is within full tollerance range. */
define('Q_MARKING_UNCALC_WITHIN_FULL_TOLERANCE', -11);

/** Error checking if the answer is within partial tollerance range. */
define('Q_MARKING_UNCALC_WITHIN_PARTIAL_TOLERANCE', -12);

/** Error checking the decimal places in the answer. */
define('Q_MARKING_UNCALC_STRICT_DP_CHECK', -13);

// End of defined values originally in /classes/question.class.php
