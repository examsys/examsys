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

/*
 * This class is used to install and update npm.
 */
class npm_utils
{
    /** Npm should install dependancies respecting the package.json file. */
    public const INSTALL = 1;

    /** Npm should get the laest versions of the depedencies. */
    public const UPDATE = 2;

    /** Npm should install dependancies respecting the package.json, skipping dev packages. */
    public const INSTALL_NODEV = 3;

    /**
     * Language pack component.
     */
    public const langcomponent = 'classes/npmutils';

    /**
     * Ensures that npm is installed, uptodate and has installed all the projects dependancies.
     *
     * @return void
     */
    public static function setup($method = self::INSTALL)
    {
        // We are going to chage the working directory and want to reset it later.
        $workingdir = getcwd();
        // Change to the root ExamSys directory.
        chdir(__DIR__ . '/..');
        self::check_for_npm();
        if ($method === self::UPDATE) {
            self::update_dependancies();
        } else {
            self::fetch_dependancies($method);
        }
        chdir($workingdir);
    }

    /**
     * Ensures npm is installed.
     *
     * @return void
     */
    protected static function check_for_npm()
    {
        $langpack = new langpack();
        exec('npm -v', $output, $statuscode);
        if ($statuscode != 0) {
            throw new Exception($langpack->get_string(self::langcomponent, 'npmmissing'));
        }
    }

    /**
     * Downloads and installs all the files required by the package.json file for the project.
     * @param integer $method install method
     * @return void
     */
    protected static function fetch_dependancies($method)
    {
        $langpack = new langpack();
        $devflag = '';
        if ($method === self::INSTALL_NODEV) {
            $devflag = '--production';
        }
        passthru("npm install $devflag", $statuscode);
        if ($statuscode != 0) {
            throw new Exception($langpack->get_string(self::langcomponent, 'couldnotinstallnpm'));
        }
        passthru('npm install --prefix plugins/texteditor/plugin_tinymce_texteditor', $statuscode);
        if ($statuscode != 0) {
            throw new Exception($langpack->get_string(self::langcomponent, 'couldnotinstallnpmtiny'));
        }
    }

    /**
     * Downloads and installs all the files required by the package.json file for the project.
     * @param integer $method update method
     * @return void
     */
    protected static function update_dependancies()
    {
        $langpack = new langpack();
        passthru('npm update', $statuscode);
        if ($statuscode != 0) {
            throw new Exception($langpack->get_string(self::langcomponent, 'couldnotupdatenpm'));
        }
        passthru('npm update --prefix plugins/texteditor/plugin_tinymce_texteditor', $statuscode);
        if ($statuscode != 0) {
            throw new Exception($langpack->get_string(self::langcomponent, 'couldnotupdatenpmtiny'));
        }
    }
}
