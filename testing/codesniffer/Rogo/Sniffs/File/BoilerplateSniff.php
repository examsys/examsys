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

namespace PHP_CodeSniffer\Standards\Rogo\Sniffs\File;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Verifies that there is a license template on each php file.
 *
 * @category  testing
 * @package   PHP_CodeSniffer
 * @author    Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright 2016 University of Nottingham
 */
class BoilerplateSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
        'PHP',
    );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_OPEN_TAG,
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the
     *                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        // Check if this is the first opening tag.
        $previous = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
        if ($previous !== false) {
            // We only want to check the Boilerplate after the initial opening tag.
            return;
        }
        $expected_boilderplate = array(
            2 => '// This file is part of ExamSys',
            3 => '//',
            4 => '// ExamSys is free software: you can redistribute it and/or modify',
            5 => '// it under the terms of the GNU General Public License as published by',
            6 => '// the Free Software Foundation, either version 3 of the License, or',
            7 => '// (at your option) any later version.',
            8 => '//',
            9 => '// ExamSys is distributed in the hope that it will be useful,',
            10 => '// but WITHOUT ANY WARRANTY; without even the implied warranty of',
            11 => '// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the',
            12 => '// GNU General Public License for more details.',
            13 => '//',
            14 => '// You should have received a copy of the GNU General Public License',
            15 => '// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.',
        );
        foreach ($expected_boilderplate as $offset => $value) {
            $next_comment = $phpcsFile->findNext(T_COMMENT, $stackPtr + $offset);
            $expected_line = $tokens[$stackPtr]['line'] + $offset;
            if ($next_comment === false) {
                // No comment found, so we need to exit.
                $message = 'Expected "%s" on line "%s"';
                $data = array(
                    $value,
                    $expected_line,
                );
                $phpcsFile->addError($message, $next_comment, 'MissingLicense', $data);
                break;
            } elseif (trim($tokens[$next_comment]['content']) !== trim($value)) {
                $message = 'Expected "%s" found "%s"';
                $data = array(
                    $value,
                    $tokens[$next_comment]['content'],
                );
                $phpcsFile->addError($message, $next_comment, 'MissingLicense', $data);
            } elseif ($tokens[$next_comment]['line'] != $expected_line) {
                $message = 'Expected "%s" on line "%s" found on line "%s" instead.';
                $data = array(
                    $value,
                    $tokens[$stackPtr]['line'] + $offset,
                    $expected_line,
                );
                $phpcsFile->addError($message, $next_comment, 'IncorrectLicense', $data);
                break;
            }
        }
    }
}
