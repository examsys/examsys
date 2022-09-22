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

namespace PHP_CodeSniffer\Standards\Rogo\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Verifies that classes can be auto loaded.
 *
 * @category  testing
 * @package   PHP_CodeSniffer
 * @author    Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright 2016 University of Nottingham
 */
class AutoloadFilenameSniff implements Sniff
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
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
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
        $filename = basename($phpcsFile->getFilename());
        if ($filename === '') {
            // No filename probably means STDIN, so we can't do this check.
            return;
        }
        $directory = dirname($phpcsFile->getFilename());
        if (mb_strpos($directory, '/unittest/tests/') !== false) {
            // Do not run in unit test directory.
            return;
        }
        $file_dir_name = mb_substr($directory, mb_strlen(dirname($directory)) + 1);
        if ($file_dir_name !== 'classes') {
            // Files will only autoload if they are in the classes directory.
            return;
        }
        $tokens = $phpcsFile->getTokens();

        $classnametoken =  $phpcsFile->findNext(T_STRING, $stackPtr);
        $classname = $tokens[$classnametoken]['content'];
        $expectedname = mb_strtolower($classname) . '.class.php';
        if ($filename === $expectedname) {
            // The file is named correctly for auto loading.
            return;
        }
        $phpcsFile->recordMetric($stackPtr, 'Invalid autoload filename', 'yes');

        $message = 'Class autoloading expects the filename to be "%s" instead "%s" was found.';
        $data = array(
            $expectedname,
            $filename,
        );
        $phpcsFile->addError($message, null, 'InvalidAutoloadFilename', $data);
    }
}
