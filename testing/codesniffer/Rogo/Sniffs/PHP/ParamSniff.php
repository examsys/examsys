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

namespace PHP_CodeSniffer\Standards\Rogo\Sniffs\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures that Param class is used to access super globals.
 * Inspired by GetRequestDataSniff by Greg Sherwood <gsherwood@squiz.net>
 *
 * @category  testing
 * @package   PHP_CodeSniffer
 * @author    Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright 2020 University of Nottingham
 */
class ParamSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_VARIABLE];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $varName = $tokens[$stackPtr]['content'];
        if (
            $varName !== '$_REQUEST'
            and $varName !== '$_GET'
            and $varName !== '$_POST'
        ) {
            return;
        }

        // The only place these super globals can be accessed directly is
        // via the Param class function fetch.
        $inClass = false;
        foreach ($tokens[$stackPtr]['conditions'] as $i => $type) {
            if ($tokens[$i]['code'] === T_CLASS) {
                $className = $phpcsFile->findNext(T_STRING, $i);
                $className = $tokens[$className]['content'];
                if (mb_strtolower($className) === 'Param') {
                    $inClass = true;
                } else {
                    // We don't have nested classes.
                    break;
                }
            } elseif ($inClass === true and $tokens[$i]['code'] === T_FUNCTION) {
                $funcName = $phpcsFile->findNext(T_STRING, $i);
                $funcName = $tokens[$funcName]['content'];
                if (mb_strtolower($funcName) === 'fetch') {
                    // This is valid.
                    return;
                } else {
                    // We don't have nested functions.
                    break;
                }
            }
        }

        // If we get to here, the super global was used incorrectly.
        $type  = 'SuperglobalAccessed';
        $error = 'The %s super global must not be accessed directly; use the checkVar function'
            . ' or the Param::required / Param::optional functions directly.';
        $data  = [$varName];

        $phpcsFile->addError($error, $stackPtr, $type, $data);
    }
}
