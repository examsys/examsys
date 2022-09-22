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

namespace PHP_CodeSniffer\Standards\Rogo\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures multi line string concats are tabbed correctly
 * Inspired by MultiLineAssignmentSniff by Greg Sherwood <gsherwood@squiz.net>
 *
 * @category  testing
 * @package   PHP_CodeSniffer
 * @author    Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright 2020 University of Nottingham
 */
class MultiLineStringConcatSniff implements Sniff
{
    /**
     * The number of spaces code should be indented.
     *
     * @var integer
     */
    public $indent = 4;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_STRING_CONCAT];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Concat character can't be the last thing on the line.
        $next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($next === false) {
            // Bad concat.
            return;
        }

        if ($tokens[$next]['line'] !== $tokens[$stackPtr]['line']) {
            $error = 'Multi-line string concat must have the concat character on the second line';
            $phpcsFile->addError($error, $stackPtr, 'ConcatCharLine');
            return;
        }

        // Make sure it is the first thing on the line, otherwise we ignore it.
        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($prev === false) {
            // Bad concat.
            return;
        }

        if ($tokens[$prev]['line'] === $tokens[$stackPtr]['line']) {
            return;
        }

        // Find the required indent based on the ident of the first line of the string declaration.
        $first = $phpcsFile->findPrevious(T_EQUAL, ($stackPtr - 1), $phpcsFile->findPrevious(T_SEMICOLON, ($stackPtr - 1)));
        // We have not found a declaration before hitting the previous statement so bail.
        if ($first == false) {
            return;
        }

        $firstLine = $tokens[$first]['line'];
        $concatIndent = 0;
        // Search backwards in tokens array until we get to a token on the firstLine.
        for ($i = ($first - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $firstLine) {
                $i++;
                break;
            }
        }

        // If token is whitespace add this to the indent level we wish to set.
        if ($tokens[$i]['code'] === T_WHITESPACE) {
            $concatIndent = $tokens[$i]['length'];
        }

        // Find the actual indent.
        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1));

        $expectedIndent = ($concatIndent + $this->indent);
        $foundIndent    = $tokens[$prev]['length'];
        if ($foundIndent !== $expectedIndent) {
            $error = 'Multi-line string concat not indented correctly; expected %s spaces but found %s';
            $data  = [
                $expectedIndent,
                $foundIndent,
            ];
            $phpcsFile->addError($error, $stackPtr, 'Indent', $data);
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'Indent', $data);
            if ($fix === false) {
                return;
            }

            $padding = str_repeat(' ', $expectedIndent);
            if ($foundIndent === 0) {
                $phpcsFile->fixer->addContentBefore($stackPtr, $padding);
            } else {
                $phpcsFile->fixer->replaceToken(($stackPtr - 1), $padding);
            }
        }
    }
}
