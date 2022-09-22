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

namespace PHP_CodeSniffer\Standards\Rogo\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures boolean operators '&&' and '||' are not used.
 * This does the opposite of the Squiz ValidLogicalOperatorSniff by Greg Sherwood <gsherwood@squiz.net>
 *
 * @category  testing
 * @package   PHP_CodeSniffer
 * @author    Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright 2020 University of Nottingham
 */
class ValidBooleanOperatorsSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_BOOLEAN_AND,
            T_BOOLEAN_OR,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The current file being scanned.
     * @param int  stackPtr   The position of the current token in the
     *                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $replacements = [
            '&&' => 'and',
            '||'  => 'or',
        ];

        $operator = mb_strtolower($tokens[$stackPtr]['content']);
        if (isset($replacements[$operator]) === false) {
            return;
        }

        $error = 'Boolean operator "%s" is prohibited; use "%s" instead';
        $data  = [
            $operator,
            $replacements[$operator],
        ];
        $phpcsFile->addError($error, $stackPtr, 'NotAllowed', $data);
    }
}
