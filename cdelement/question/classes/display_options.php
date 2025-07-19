<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question element - Display options of the question.
 *
 * @package   cdelement_question
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_question;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/engine/lib.php');

/**
 * Display options class to speific the questions display behaviour.
 */
class display_options extends \question_display_options {

    /** @var int Read only. */
    public const READONLY = 1;

    /** @var int Maximum mark.*/
    public const MAX_MARK = 1;

    /** @var string the behaviour to use for this preview. */
    public $behaviour;

    /** @var number the maximum mark to use for this preview. */
    public $maxmark;

    /** @var int the variant of the question to preview. */
    public $variant;

    /** @var string if set, force the embedded question to be displayed in this language. Moodle lang code, e.g. 'fr'. */
    public $forcedlanguage = null;

    /** @var int whether the current user is allowed to see the 'Fille with correct' button. */
    public $fillwithcorrect = self::HIDDEN;

    /** @var string Accessibility text for the iframe. */
    public $iframedescription = '';

    /** @var int whether the current user is allowed to see the 'Question bank' link. */
    public $showquestionbank = self::HIDDEN;

    /**
     * Constructor.
     * @param bool $behaviour
     */
    public function __construct($behaviour=null) {

        $defaults = get_config('cdelement_question');

        $this->behaviour = $behaviour ?: 'adaptive';
        $this->maxmark = self::MAX_MARK;

        $this->correctness = $defaults->correctness ?? self::VISIBLE;
        $this->marks = $defaults->marks ?? self::VISIBLE;
        $this->markdp = $defaults->decimalplaces ?? self::VISIBLE;
        $this->feedback = $defaults->feedback ?? self::VISIBLE;
        $this->numpartscorrect = $defaults->feedback ?? self::VISIBLE;
        $this->generalfeedback = $defaults->generalfeedback ?? self::VISIBLE;
        $this->rightanswer = $defaults->rightanswer ?? self::VISIBLE;
        $this->history = $defaults->history ?? self::VISIBLE;
        $this->forcedlanguage = $defaults->forcedlanguage ?? '';
    }

}
