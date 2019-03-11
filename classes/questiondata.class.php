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

/**
* Question Data package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2018 onwards The University of Nottingham
*/

/**
 * Question data helper class.
 * Get/Set data to be used in rendering object.
 */
abstract class questiondata {
  /**
   * Media type - file
   */
  const FILE = 1;
  /**
   * Media type - image
   */
  const IMAGE = 2;
  /**
   * Media type - audio
   */
  const AUDIO = 3;
  /**
   * Media type - document
   */
  const DOC = 4;
  /**
   * Media type - flash
   */
  const FLASH = 5;
  /**
   * Media type - mp3
   */
  const MP3 = 6;
  /**
   * Media type - movie
   */
  const MOVIE = 7;
  /**
   * Media type - 3d object
   */
  const THREED = 8;
  /**
   * Media type - archive
   */
  const ARCHIVE = 9;
  /**
   * DB connection
   * @var mysqli 
   */
  private $db;

  /**
   * Config object
   * @var object
   */
  protected $config;

  /**
   * Question answered state
   * @var boolean
   */
  public $unanswered;

  /**
   * Question unanswered key state
   * @var boolean
   */
  public $unansweredkey;

  /**
   * Colour of 'labels' in paper
   * @var string
   */
  public $labelcolour;

  /**
   * Question settings
   * @var json
   */
  public $settings;
  
  /**
   * Calculator state of question
   * @var boolean
   */
  public $displaycalc;

  /**
   * Prologue state in paper
   * @var boolean
   */
  public $displayprologue;

  /**
   * Theme state of question
   * @var boolean
   */
  public $displaytheme;

  /**
   * Media state of question
   * @var boolean
   */
  public $displaymedia;

  /**
   * Scenario state of question
   * @var boolean
   */
  public $displayscenario;

  /**
   * Notes state of question
   * @var boolean
   */
  public $displaynotes;

  /**
   * Leadin state of question
   * @var boolean
   */
  public $displayleadin;

  /**
   * Question header state
   * @var boolean
   */
  public $displaydefault;

  /**
   * Negative marking state of question
   * @var boolean
   */
  public $negativemarking;

  /**
   * Display method used by question
   * @var string
   */
  public $displaymethod;

  /**
   * Display state of option media
   * @var boolean
   */
  public $displayoptionmedia;

  /**
   * Question scenario
   * @var string
   */
  public $scenario;

  /**
   * Question notes
   * @var string
   */
  public $notes;

  /**
   * Question media
   * @var string
   */
  public $qmedia;

  /**
   * Question media height
   * @var string
   */
  public $qmediaheight;

  /**
   * Question media width
   * @var string
   */
  public $qmediawidth;

  /**
   * Question type
   * @var string
   */
  public $questiontype;

  /**
   * Question options
   * @var array
   */
  public $options;

  /**
   * Paper prologue
   * @var string
   */
  public $prologue;

  /**
   * Question theme
   * @var string
   */
  public $theme;

  /**
   * Question number of options
   * @var integer
   */
  public $optionnumber;

  /**
   * Paper type
   * @var string
   */
  public $papertype;

  /**
   * Question leadin
   * @var string
   */
  public $leadin;

  /**
   * Question langauge
   * @var string
   */
  public $language;

  /**
   * Question assigned display number
   * @var boolean
   */
  public $assignednumber;

  /**
   * Question media id
   * @var integer
   */
  public $mediaid;

  /**
   * Question media filename
   * @var string
   */
  public $mediafile;

  /**
   * Question media width
   * @var integer
   */
  public $mediawidth;
  /**
   * Question media height
   * @var integer
   */

  public $mediaheight;

  /**
   * Question media url
   * @var string
   */
  public $mediaurl;

  /**
   * Question media url
   * @var string
   */
  public $mediatype;

  /**
   * Question media border state
   * @var boolean
   */
  public $mediaborder;

  /**
   * Question media border colour
   * @var string
   */
  public $mediabordercolour;

  /**
   * Question media extenstion value
   * @var string
   */
  public $mediaext;

  /**
   * Question media delay render flag
   * @var string
   */
  public $mediadelay;

  /**
   * Extra settings for media
   * @var string
   */
  public $mediaextra;

  /**
   * Question media edit state
   * @var boolean
   */
  public $mediaedit;

  /**
   * Question media delete state
   * @var boolean
   */
  public $mediadelete;

  /**
   * Question display number
   * @var integer
   */
  public $questionno;

  /**
   * Question part id
   * @var integer
   */
  public $partid;

  /**
   * Marks for question
   * @var float
   */
  public $finalmarks;

  /**
   * Question score method
   * @var string
   */
  public $scoremethod;
  
  /**
   * Question bonus type
   * @var string
   */
  public $bonus;

  /**
   * Question b available marks
   * @var float
   */
  public $marks;
  
  /**
   * Order of question options
   * @var string 
   */
  public $optionorder;

  /**
   * Question object name
   * @var string 
   */
  public $object;

  /**
   * The current question
   * @var array 
   */
  public $question;

  /**
   * User answers
   * @var array 
   */
  public $useranswers;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  /**
   * Constructor
   */
  function __construct() {
    $this->config = Config::get_instance();
    $this->db = $this->config->db;
    $this->unanswered = false;
    $this->labelcolour = '#C00000';
    $this->displaycalc = true;
    $this->displayprologue = false;
    $this->displaytheme = false;
    $this->displaymedia = false;
    $this->displayscenario = false;
    $this->displaynotes = false;
    $this->displayleadin = false;
    $this->displaydefault = false;
    $this->negativemarking = false;
    $this->displaymethod = '';
    $this->displayoptionmedia = false;
  }

  /**
   * Abstract function to set question header
   * @return void
   */
  abstract public function set_question_head();

  /**
   * Abstract function to set question
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param integer $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @return void
   */
  abstract public function set_question($screen_pre_submitted, $useranswer, $userdismissed);

  /**
   * Abstract function to set question options
   * @param integer $part_id part loop id
   * @param integer $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @return void
   */
  abstract public function set_option_answer($part_id, $useranswer, $userdismissed, $screen_pre_submitted);

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param integer $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @return void
   */
  abstract public function process_options($part_id, $useranswer, $userdismissed, $screen_pre_submitted);

  /**
   * Get total marks for question
   * @param float $markscorrect marks for correct answer
   * @reutrn float
   */
  abstract public function get_marks ($markscorrect);

  /**
   * Get options
   * @param integer $id option id
   * @return array
   */
  public function get_opt($id) {
    if (empty($this->options[$id])) {
      return array();
    } else {
      return $this->options[$id];
    }
  }

  /**
   * Set options
   * @param integer $id option id
   * @param array $opt options
   * @return void
   */
  public function set_opt($id, $opt) {
    $this->options[$id] = $opt;
  }

  /**
   * Return the base mark for the question type
   * @return int
   */
  public function get_base_marks() {
    return 0;
  }

  /* Render the question to screen
   * @param object $render twig rendering object
   * @param array $string language strings
   * @return void
   */
  public function render_question($render, $string) {
    // Check if the display method has its own template. Otherwise use default template.
    if (file_exists(dirname(__DIR__)
      . DIRECTORY_SEPARATOR . 'plugins'
      . DIRECTORY_SEPARATOR . 'questions'
      . DIRECTORY_SEPARATOR
      . $this->questiontype
      . DIRECTORY_SEPARATOR
      . 'templates' . '/' . $this->questiontype . '_' . $this->displaymethod . '.html')){
      $this->questiontemplate = $this->questiontype . '_' . $this->displaymethod . '.html';
    } else {
      $this->questiontemplate = $this->questiontype . '.html';
    }
    $render->render($this, $string, 'paper/question.html');
  }

  /**
   * Setup question
   * @global array $used_questions user log data for questions
   * @global array $user_dismiss user dismiss data for questions
   * @global array $user_order the order the user gets the question options
   * @global string $language system language
   * @param boolean $screen_pre_submitted has the user been on this screen before
   * @param integer $q_displayed loop id of question
   * @param string $string language strings
   * @param array $question question data
   * @param integer $pid paper id
   * @param integer $current_screen current screen id
   * @param integer $question_no current question number
   * @param array $user_answers users answers
   */
  public function setup_question_data($screen_pre_submitted, $q_displayed, $string, &$question, $pid, $current_screen, &$question_no, $user_answers) {
    global $used_questions, $user_dismiss, $user_order, $language;

    $paper_properties = PaperUtils::get_paper_properties($pid, $this->db);
    
    // Attempt to display paper prolog
    if ($q_displayed == 0 and $current_screen == 1 and $paper_properties['paper_prologue'] != '') {
      $this->prologue = $paper_properties['paper_prologue'];
      $this->displayprologue = true;
    }

    $q_id = $question['q_id'];
    $option_no = count($question['options']);
    $this->optionnumber = $option_no;
    // Determine if negative marking is used.
    $neg_marking = false;
    if (isset($question['object']) and method_exists($question['object'], 'is_negative_marked')) {
      $neg_marking = $question['object']->is_negative_marked();
    } else {
      foreach ($question['options'] as $tmp_option) {
        if ($tmp_option['marks_incorrect'] < 0) $neg_marking = true;
      }
    }
    $this->negativemarking = $neg_marking;

    // Process the order
    $question['option_order'] = array();
    if (isset($question['q_option_order']) and ($question['q_option_order'] == 'random' or $question['q_option_order'] == 'alphabetic')) {
      if (!isset($user_order[$current_screen][$q_id]) or $user_order[$current_screen][$q_id] == '') {
        if ($question['q_option_order'] == 'random') {
          for ($i=0; $i<$option_no; $i++) {
            $question['option_order'][$i] = $i;
          }
          shuffle($question['option_order']);
        } elseif ($question['q_option_order'] == 'alphabetic') {
          $tmp_order_array = array();
          for ($i=0; $i<$option_no; $i++) {
            $tmp_order_array[$i] = strtolower($question['options'][$i]['option_text']);
          }
          asort($tmp_order_array);
          foreach ($tmp_order_array as $key => $value) {
            $question['option_order'][]= $key;
          }
        } else {
          // Make up the order array in the existing order
          for ($i=0; $i<$option_no; $i++) {
            $question['option_order'][$i] = $i;
          }
        }
      } else {
        // Set the order array to what is stored in the users log record
        $question['option_order'] = explode(',', $user_order[$current_screen][$q_id]);
      }

      // Re-arrange the options array
      $new_options = array();
      for ($i=0; $i<$option_no; $i++) {
        $new_options[$i] = $question['options'][$question['option_order'][$i]];
      }
      $question['options'] = $new_options;
    } else {
      // Make up the order array in the existing order
      for ($i=0; $i<$option_no; $i++) {
        $question['option_order'][$i] = $i;
      }
    }

    // info blocks do not count towards question number.
    if ($question['q_type'] !== 'info') {
      $question_no++;
    }

    if ($question['theme'] != '') {
      $this->theme = $question['theme'];
      $this->displaytheme = true;
    }

    $this->papertype = $paper_properties['type'];
    $this->assignednumber =  $question['assigned_number'];
    $this->scenario = $question['scenario'];
    $this->notes = $question['notes'];
    $this->qmedia = $question['q_media'];
    $this->qmediawidth = $question['q_media_width'];
    $this->qmediaheight = $question['q_media_height'];
    $this->leadin = $question['leadin'];
    $this->language = $language;
    $this->settings = $question['settings'];
    if (isset($question['object'])) {
      $this->object = $question['object'];
    }
    $this->question = $question;
    $this->useranswers = $user_answers;
    $this->set_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');

    // Set question header.
    $this->set_question_head();

    $part_id = 0;

    // What is the users current answer.
    if (isset($user_answers[$current_screen][$q_id])) {
      $useranswer = $user_answers[$current_screen][$q_id];
    } else {
      $useranswer = null;
    }

    // What is the users current dismissed.
    if (isset($user_dismiss[$current_screen][$q_id])) {
      $userdismissed = $user_dismiss[$current_screen][$q_id];
    } else {
      $userdismissed = null;
    }

    // Pre-question processing
    $this->questionno = $question_no;
    $this->displaymethod = $question['display_method'];
    $this->scoremethod = $question['score_method'];
    $this->set_question($screen_pre_submitted, $useranswer, $userdismissed);

    // Processing for each stem.
    $this->options = array();
    $this->marks = $this->get_base_marks();
    foreach ($question['options'] as $display_option) {
      $part_id++;
      $this->partid = $part_id;
      $tmp_part_id = $question['option_order'][$part_id-1] + 1;
      $this->set_opt($part_id, array(
          'optiontext' => $display_option['option_text'],
          'omedia' => $display_option['o_media'],
          'markscorrect' => $display_option['marks_correct'],
          'marksincorrect' => $display_option['marks_incorrect'],
          'correct' => $display_option['correct'],
          'optionno' => 'q' . $this->questionno . '_' . $tmp_part_id,
          'position' => $tmp_part_id
      ));
      $this->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '', false, -1, false, $part_id);

      // Set question options.
      $this->set_option_answer($part_id, $useranswer, $userdismissed, $screen_pre_submitted);
    }

    $this->optionorder = implode(',', $question['option_order']);

    $this->process_options($part_id, $useranswer, $userdismissed, $screen_pre_submitted);

    $this->finalmarks = $this->get_marks($display_option['marks_correct']);;
    if ($paper_properties['type'] < 3) {
      if ($this->finalmarks != 0) {
        if ($question['score_method'] == 'Bonus Mark') {
          $this->scoremethod = 'bonus';
          $plural = ($display_option['marks_correct'] == 1) ?  $string['mark'] : $string['marks'];
          $this->bonus = sprintf($string['bonusmark'], $display_option['marks_correct'], $plural);  // Used on ranking questions
        }
      }
    }

    $used_questions[$q_id] = $q_id;
  }

  /**
   * Set question media
   *
   * @param string $filename media file name
   * @param integer $width media width
   * @param integer $height media height
   * @param string $border_color media border colour
   * @param boolean $delay delay media rendering on screen
   * @param integer $imageid media id
   * @param boolean $locked is media locked
   * @param string $part_id option part id
   */
  public function set_media($filename, $width, $height, $border_color, $delay=false, $imageid=-1, $locked=false, $part_id=null) {

    $mediadirectory = rogo_directory::get_directory('media');
    $fn_parts = pathinfo($filename);
    $mediaedit = false;
    $mediadelete = false;
    $mediatype = null;
    $mediaborder = true;
    $url = $mediadirectory->url($filename);
    $extra = array();

    // Set file type.
    $ext = '';
    if (!array_key_exists('extension', $fn_parts)) {
      $mediatype = self::FILE;
    } else {
      $ext = strtolower($fn_parts['extension']);
      if (key_exists($ext, \media_handler::SUPPORTED)) {
        // Supported types.
        $mediatype = \media_handler::SUPPORTED[$ext];
      } elseif ($ext == 'flv') {
        // Deprecated type that can no longer be added but can be displayed.
        $mediatype = self::FLASH;
      } elseif ($ext == 'wmv') {
        // Deprecated type that can no longer be added but can be displayed.
        $mediatype = self::MOVIE;
      }

      // Additional display logic.
      switch ($mediatype) {
        case self::IMAGE:
          if ($border_color == '') {
            $mediaborder = false;
          }
          break;
        case self::MP3:
          // Display filename if add or edit script
          if (strpos(Url::fromGlobals(),'/edit/') !== false or strpos(Url::fromGlobals(),'/add/') !== false) {
            $mediaedit = true;
          }
          break;
        case self::FLASH:
          if ($width == 0 or $height == 0) {
            $width = 320;
            $height = 260;
          }
          $url = $mediadirectory->url($filename, false, false, true);
          break;
        case self::THREED:
          $width = 640;
          $height = 480;
          break;
        case self::ARCHIVE:
          // Currently we only expect this to be and obj file with materials.
          // We search the archive for the first obj and mtl files (subsequent files of these types are ignored).
          // Error if we do not have an obj and a mtl file.
          $info = pathinfo($filename);
          $dir = new DirectoryIterator($mediadirectory->location() . $info['filename']);
          $foundobj = false;
          $foundmtl = false;
          foreach ($dir as $fileinfo) {
            $filename = $fileinfo->getFilename();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($ext === 'obj' and $foundobj === false) {
              $width = 640;
              $height = 480;
              $foundobj = true;
              $extra['obj'] = $filename;
            } elseif ($ext === 'mtl' and $foundmtl === false) {
              $extra['mtl'] = $filename;
              $foundmtl = true;
            }
          }
          if (!$foundobj) {
            // If mtl file not found object skeleton displayed.
            // Error if obj file not found - set to file so not displayed.
            $this->mediatype = questiondata::FILE;
          }
          break;
        default:
          break;
      }
    }

    if ($imageid > -1 and !$locked) {
      $mediadelete = true;
    }

    // Set option media to question media.
    if (!is_null($part_id)) {
      $option = $this->get_opt($part_id);
      $option['optionmedia'] = array(
          'mediaid' => $imageid,
          'mediafile' => $filename,
          'mediawidth'=> $width,
          'mediaheight'=> $height,
          'mediaurl' => $url,
          'mediadelete' => $mediadelete,
          'mediaedit' => $mediaedit,
          'mediatype' => $mediatype,
          'mediaborder' => $mediaborder,
          'mediabordercolour' => $border_color,
          'mediaext' => $ext,
          'mediadelay' => $delay,
          'mediaextra' => $extra,
      );
      $this->set_opt($part_id, $option);
    } else {
      $this->mediaid = $imageid;
      $this->mediafile = $filename;
      $this->mediawidth = $width;
      $this->mediaheight = $height;
      $this->mediaurl = $url;
      $this->mediadelete = $mediadelete;
      $this->mediaedit = $mediaedit;
      $this->mediatype = $mediatype;
      $this->mediaborder = $mediaborder;
      $this->mediabordercolour = $border_color;
      $this->mediaext = $ext;
      $this->mediadelay = $delay;
      $this->mediaextra = $extra;
    }
  }

  /**
   * Get data store class if it exists
   * @param string $qtype question type
   */
  public static function get_datastore($qtype) {
    if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'questions' . DIRECTORY_SEPARATOR . $qtype)) {
      $questionpluginns = 'plugins\\questions\\' . $qtype . '\\renderdata';
    } else {
      $questionpluginns = 'plugins\\questions\\undefined\\renderdata';
    }
    return new $questionpluginns();
  }
}