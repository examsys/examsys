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

namespace testing\behat\steps\database;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use module_utils;
use PaperUtils;
use StudentNotes;
use testing\behat\helpers\database\state;
use testing\datagenerator\data_error;
use UserUtils;

/**
 * Core steps that add data into the Rogo database.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait datageneration
{
    /**
     * Maps the types that can be passed to exist to a data generator.
     *
     * The values should be an array where:
     * the first value is the name of the data generator,
     * the second value should be the data generator's component,
     * the third value is the function that should be called to create the data.
     * the forth value is the pre process function run before the data generation
     *
     * @var array
     */
    protected $datagenerator_map = array(
        'users' => array('users', 'core', 'create_user', null),
        'papers' => array('papers', 'core', 'create_paper', 'preProcessPaper'),
        'questions' => array('questions', 'core', 'create_question', 'preProcessQuestion'),
        'modules' => array('modules', 'core', 'create_module', null),
        'academic year' => array('academic_year', 'core', 'create_academic_year', null),
        'module team members' => array('modules', 'core', 'create_module_team', null),
        'config' => array('config', 'core', 'change_setting', null),
        'campuses' => array('labs', 'core', 'create_campus', null),
        'labs' => array('labs', 'core', 'create_lab', null),
        'exam pcs' => array('labs', 'core', 'create_exam_pc', null),
        'module keywords' => array('modules', 'core', 'createModuleKeywords', null),
        'module enrolment' => array('modules', 'core', 'create_enrolment', 'preProcessmoduleEnrolment'),
        'paper note' => array('users', 'core', 'addPaperNote', 'preProcessPaperNote'),
    );

    /**
     * Will return an array that contains only the indicies from $defaults,
     * using the values from $paramterts if they are set.
     *
     * @param array $defaults Contains all the indicies that should be returned along their their default values.
     * @param array $parameters The values that should over write the defaults.
     * @return array
     */
    protected function set_defaults_and_clean(array $defaults, array $parameters)
    {
        $return = array_merge($defaults, $parameters);
        return array_intersect_key($return, $defaults);
    }

    /**
     * Generate a blank option string
     * @param array $row the question data
     * @return string
     */
    private function getBlankOptions(array $row): string
    {
        $defaults = [
            'blanks' => '',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        return '{"option_text":"' . $row['blanks']
            . '","marks_correct":"' . $row['marks_correct']
            . '","marks_incorrect":"' . $row['marks_incorrect']
            . '","marks_partial":"' . $row['marks_partial'] . '"}';
    }

    /**
     * Generate a textbox option string
     * @param array $row the question data
     * @return string
     */
    private function getTextBoxOptions(array $row): string
    {
        $defaults = [
            'correct' => 'placeholder',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        return '{"correct":"' . $row['correct']
            . '","marks_correct":"' . $row['marks_correct']
            . '","marks_incorrect":"' . $row['marks_incorrect']
            . '","marks_partial":"' . $row['marks_partial'] . '"}';
    }

    /**
     * Generate a true_false option string
     * @param array $row the question data
     * @return string
     */
    private function getTrueFalseOptions(array $row): string
    {
        if ($row['correct'] == 'false') {
            $row['correct'] = 'f';
        } else {
            $row['correct'] = 't';
        }

        $defaults = [
            'correct' => 't',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        return '{"correct":"' . $row['correct']
            . '","marks_correct":"' . $row['marks_correct']
            . '","marks_incorrect":"' . $row['marks_incorrect']
            . '","marks_partial":"' . $row['marks_partial'] . '"}';
    }

    /**
     * Generate a mcq option string
     * @param array $row the question data
     * @return string
     */
    private function getMCQOptions(array $row): string
    {
        $defaults = [
            'num_options' => 2,
            'correct' => 1,
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $row['options'] = '[';
        for ($i = 0; $i < $row['num_options']; $i++) {
            $needle = $i + 1;
            $row['options'] .= '{"option_text":"option ' . $needle
                . '","correct":"' . $row['correct']
                . '","marks_correct":"' . $row['marks_correct']
                . '","marks_incorrect":"' . $row['marks_incorrect']
                . '","marks_partial":"' . $row['marks_partial'] . '"},';
        }
        return rtrim($row['options'], ',') . ']';
    }

    /**
     * Generate a mrq / dichotomous option string
     * @param array $row the question data
     * @return string
     */
    private function getMRQOptions(array $row): string
    {
        $defaults = [
            'num_options' => 2,
            'type' => 'dichotomous',
            'correct_options' => '1',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $row['options'] = '[';
        for ($i = 0; $i < $row['num_options']; $i++) {
            $correct = 'n';
            if ($row['type'] === 'dichotomous') {
                $correct = 'f';
            }
            $needle = $i + 1;
            if (array_search($needle, explode(',', $row['correct_options'])) !== false) {
                $correct = 'y';
                if ($row['type'] === 'dichotomous') {
                    $correct = 't';
                }
            }
            $row['options'] .= '{"option_text":"option ' . $needle
                . '","correct":"' . $correct
                . '","marks_correct":"' . $row['marks_correct']
                . '","marks_incorrect":"' . $row['marks_incorrect']
                . '","marks_partial":"' . $row['marks_partial'] . '"},';
        }
        return rtrim($row['options'], ',') . ']';
    }

    /**
     * Generate a rank option string
     * @param array $row the question data
     * @return string
     */
    private function getRankOptions(array $row): string
    {
        $defaults = [
            'num_options' => 3,
            'correct_order' => '1,2,3',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $row['options'] = '[';
        $correct = explode(',', $row['correct_order']);
        for ($i = 0; $i < $row['num_options']; $i++) {
            $needle = $i + 1;
            $row['options'] .= '{"option_text":"option ' . $needle
                . '","correct":"' . $correct[$i]
                . '","marks_correct":"' . $row['marks_correct']
                . '","marks_incorrect":"' . $row['marks_incorrect']
                . '","marks_partial":"' . $row['marks_partial'] . '"},';
        }
        return rtrim($row['options'], ',') . ']';
    }

    /**
     * Generate a sct option string
     * @param array $row the question data
     * @return string
     */
    private function getSCTOptions(array $row): string
    {
        $defaults = [
            'experts' => '1,2,10,3,1',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $row['options'] = '[';
        for ($i = 0; $i < 5; $i++) {
            $correct = explode(',', $row['experts']);
            $text = array(
                'very unlikely',
                'unlikely',
                'neither likely nor unlikely',
                'more likely',
                'very likely'
            );
            $row['options'] .= '{"option_text":"' . $text[$i]
                . '","correct":"' . $correct[$i]
                . '","marks_correct":"' . $row['marks_correct']
                . '","marks_incorrect":"' . $row['marks_incorrect']
                . '","marks_partial":"' . $row['marks_partial'] . '"},';
        }
        return rtrim($row['options'], ',') . ']';
    }

    /**
     * Generate a extmatch/matrix option string
     * @param array $row the question data
     * @return string
     */
    private function getExtMatchOptions(array $row): string
    {
        $defaults = [
            'num_options' => 3,
            'correct_options' => '3,1,2',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $row['options'] = '[';
        $correct = str_replace(',', '|', $row['correct_options']) . '|';
        // Pad out pipes to max.
        $count = substr_count($correct, '|');
        if ($count < 9) {
            $append = str_repeat('|', 9 - $count);
            $correct .= $append;
        }
        for ($i = 0; $i < $row['num_options']; $i++) {
            $needle = $i + 1;
            $row['options'] .= '{"option_text":"option ' . $needle
                . '","correct":"' . $correct
                . '","marks_correct":"' . $row['marks_correct']
                . '","marks_incorrect":"' . $row['marks_incorrect']
                . '","marks_partial":"' . $row['marks_partial'] . '"},';
        }
        return rtrim($row['options'], ',') . ']';
    }

    /**
     * Generate a calc settings string
     * @param array $row the question data
     * @return string
     */
    private function getCalcSettings(array $row): string
    {
        $defaults = [
            'tolerance_full' => 0,
            'tolerance_partial' => 0,
            'variables' => '{}',
            'dp' => 0,
            'strictdisplay' => 'true',
            'strictzeros' => 'false',
            'fulltoltyp' => '#',
            'parttoltyp' => '#',
            'marks_unit' => 0,
            'show_units' => 'true',
            'formula' => '',
            'formula_units' => '""',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        return '{"tolerance_full":"' . $row['tolerance_full']
            . '","tolerance_partial":"' . $row['tolerance_partial']
            . '","vars":' . $row['variables']
            . ',"marks_correct":' . $row['marks_correct']
            . ',"marks_incorrect":' . $row['marks_incorrect']
            . ',"marks_partial":' . $row['marks_partial']
            . ',"dp":"' . $row['dp']
            . '","strictdisplay":' . $row['strictdisplay']
            . ',"strictzeros":' . $row['strictzeros']
            . ',"fulltoltyp":"' . $row['fulltoltyp']
            . '","parttoltyp":"' . $row['parttoltyp']
            . '","marks_unit":' . $row['marks_unit']
            . ',"show_units":' . $row['show_units']
            . ',"answers":[{"formula":"' . $row['formula']
            . '","units":' . $row['formula_units'] . '}]}';
    }

    /**
     * Generate a textbox settings string
     * @param array $row the question data
     * @return string
     */
    private function getTextBoxSettings(array $row): string
    {
        $defaults = [
            'columns' => 80,
            'rows' => 4,
            'editor' => 'Plain Text',
            'terms' => '[]',
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        return '{"columns":"' . $row['columns']
            . '","rows":"' . $row['rows']
            . '","editor":"' . $row['editor']
            . '","terms":"' . $row['terms'] . '"}';
    }

    /**
     * Preprocess question data
     * @param array $row question data row
     * @return array
     */
    private function preProcessQuestion(array $row): array
    {
        // Defaults.
        if (empty($row['marks_correct'])) {
            $row['marks_correct'] = 1;
        }
        if (empty($row['marks_incorrect'])) {
            $row['marks_incorrect'] = 0;
        }
        if (empty($row['marks_partial'])) {
            $row['marks_partial'] = 0;
        }
        // Force display method for sct.
        if ($row['type'] === 'sct') {
            $row['display_method'] = 1;

            // Optional new information needs to be added to the leadin.
            if (isset($row['newinfo'])) {
                $row['leadin'] .= '~' . $row['newinfo'];
            }

            // The SCT leadin is special. and contains multiple pices of information separated by a ~
            // The second part is for new information.
            if (strpos($row['leadin'], '~') === false) {
                $row['leadin'] .= '~';
            }
        }
        // Generate option json.
        switch ($row['type']) {
            case 'blank':
                $row['options'] = $this->getBlankOptions($row);
                break;
            case 'textbox':
                $row['options'] = $this->getTextBoxOptions($row);
                break;
            case 'true_false':
                $row['options'] = $this->getTrueFalseOptions($row);
                break;
            case 'mcq':
                $row['options'] = $this->getMCQOptions($row);
                break;
            case 'dichotomous':
            case 'mrq':
                $row['options'] = $this->getMRQOptions($row);
                break;
            case 'rank':
                $row['options'] = $this->getRankOptions($row);
                break;
            case 'sct':
                $row['options'] = $this->getSCTOptions($row);
                break;
            case 'matrix':
            case 'extmatch':
                $row['options'] = $this->getExtMatchOptions($row);
                break;
            default:
                break;
        }
        // Generate scenario.
        if ($row['type'] === 'extmatch' or $row['type'] === 'matrix') {
            $row['scenario'] = '';
            for ($i = 0; $i < $row['num_stems']; $i++) {
                $needle = $i + 1;
                $row['scenario'] .= 'stem ' . $needle . '|';
            }
        }
        // Generate settings json.
        switch ($row['type']) {
            case 'textbox':
                $row['settings'] = $this->getTextBoxSettings($row);
                break;
            case 'enhancedcalc':
                $row['settings'] = $this->getCalcSettings($row);
                break;
            default:
                break;
        }
        // Generate paper json if assigned to a paper.
        if (isset($row['paper'])) {
            $row['paper'] = '{"paper":"' . $row['paper'] . '","screen":"' . $row['screen'] . '","displaypos":"' . $row['position'] . '"}';
        }
        return $row;
    }

    /**
     * Preprocess paper data
     * @param array $row paper data row
     * @return array
     */
    private function preProcessPaper(array $row): array
    {
        // Get paper type.
        $types = \PaperUtils::getTypeList();
        $row['papertype'] = $types[$row['type']];
        // Generate settings json.
        $row['settings'] = '';
        if ($row['type'] === 'osce') {
            switch ($row['marking']) {
                case 'Pass | Fail':
                    $marking = 7;
                    break;
                case 'Clear FAIL | BORDERLINE | Clear PASS | Honours PASS':
                    $marking = 6;
                    break;
                case 'N/A':
                    $marking = 5;
                    break;
                case 'Fail | Borderline fail | Borderline pass | Pass | Good pass':
                    $marking = 4;
                    break;
                case 'Clear Fail | Borderline | Clear Pass':
                    $marking = 3;
                    break;
                default:
                    $marking = 5;
                    break;
            }
            $row['settings'] = '{"marking":"' . $marking . '"}';
        }
        return $row;
    }

    /**
     * Enrol a student onto a module
     *
     * @param array $row module enrolment data row
     * @return array
     * @throws data_error
     */
    private function preProcessmoduleEnrolment(array $row): array
    {
        if (empty($row['modulecode'])) {
            throw new data_error('modulecode must be provided');
        }
        if (empty($row['sid'])) {
            throw new data_error('sid must be provided');
        }
        if (empty($row['attempt'])) {
            $row['attempt'] = 1;
        }
        if (empty($row['calendar_year'])) {
            $row['calendar_year'] = null;
        }
        if (empty($row['auto_update'])) {
            $row['auto_update'] = 0;
        }
        $row['userid'] = UserUtils::studentid_exists($row['sid'], state::get_db());
        $row['moduleid'] = module_utils::get_idMod($row['modulecode'], state::get_db());
        unset($row['sid']);
        unset($row['modulecode']);
        return $row;
    }

    /**
     * Add a student note to a paper
     *
     * @param array $row paper note data row
     * @return array
     * @throws data_error
     */
    private function preProcessPaperNote(array $row): array
    {
        if (empty($row['user'])) {
            throw new data_error('user must be provided');
        }
        if (empty($row['paper'])) {
            throw new data_error('paper must be provided');
        }
        if (empty($row['author'])) {
            throw new data_error('author must be provided');
        }

        if (empty($row['note'])) {
            $row['note'] = '';
        }
        $row['userID'] = UserUtils::username_exists($row['user'], state::get_db());
        $row['paperID'] = PaperUtils::getPaperId($row['paper']);
        $row['authorID'] = UserUtils::username_exists($row['author'], state::get_db());
        $existingnote = StudentNotes::get_note($row['paperID'], $row['userID'], state::get_db());
        if ($existingnote === false) {
            $row['noteID'] = 0;
        } else {
            $row['noteID'] = $existingnote['note_id'];
        }
        unset($row['user']);
        unset($row['paper']);
        unset($row['author']);
        return $row;
    }

    /**
     * Adds records to the database using an appropriate data generator.
     *
     * @Given /^the following "([^"]*)" exist:$/
     * @param string $type The type of data to be generated. It must appear in self::$datagenerator_map
     * @param TableNode $data The data to be loaded
     * @throws PendingException if the type is not mapped, or the generator function does not exist
     */
    public function the_following_exist($type, TableNode $data)
    {
        if (!isset($this->datagenerator_map[$type])) {
            // The type has not yet been mapped.
            // We should let the user know that it needs to be implemented.
            throw new PendingException("Implement a data generator for: $type");
        }
        $generatorname = $this->datagenerator_map[$type][0];
        $generatorcomponent = $this->datagenerator_map[$type][1];
        $createmethod = $this->datagenerator_map[$type][2];
        $datagenerator = $this->get_datagenerator($generatorname, $generatorcomponent);
        // Check the creation method exists.
        if (!method_exists($datagenerator, $createmethod)) {
            $message = "Implement the {$createmethod} method in the "
                . "{$generatorcomponent}_{$generatorname} data generator";
            throw new PendingException($message);
        }
        // Get the preprocess function.
        $preprocess = $this->datagenerator_map[$type][3];
        // Convert the data into a form that the data generator can use.
        foreach ($data->getHash() as $row) {
            // Preprocess each row.
            if (!is_null($preprocess)) {
                $row = $this->$preprocess($row);
            }
            // Pass each row into the generator.
            $datagenerator->$createmethod($row);
        }
    }
}
