<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_stickynotes configuration form.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_stickynotes_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('stickynotesname', 'mod_stickynotes'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Choose colors ?
        $mform->addElement('advcheckbox', 'colors', get_string('colors', 'stickynotes'));
        $mform->addHelpButton('colors', 'colors', 'stickynotes');

        // Show authors ?
        $mform->addElement('advcheckbox', 'viewauthor', get_string('viewauthor', 'stickynotes'));
        $mform->addHelpButton('viewauthor', 'viewauthor', 'stickynotes');

        // Enable the limitation of notes ?
        $mform->addElement('advcheckbox', 'limitstickynotes', get_string('limitstickynotes', 'stickynotes'));
        $mform->addHelpButton('limitstickynotes', 'limitstickynotes', 'stickynotes');

        // Max number of notes.
        $mform->disabledIf('maxstickynotes', 'limitstickynotes', '0');
        $mform->addElement('text', 'maxstickynotes', get_string('maxstickynotes', 'stickynotes'));
        $mform->setType('maxstickynotes', PARAM_INT);
        $mform->addRule('maxstickynotes', get_string('maxstickynoteserror', 'stickynotes'), 'regex', '/^[0-9]+$/', 'client');

        // Adding the "Votes" fieldset.
        // Define votes types :0 = no votes, 1 = user can like a vote.
        $options = array(
            '0'      => get_string('votenone', 'stickynotes'),
            '1'      => get_string('votelike', 'stickynotes')
        );

        $mform->addElement('select', 'votes', get_string('votetype', 'stickynotes'), $options);
        $mform->addHelpButton('votes', 'votetype', 'stickynotes');

        // Enable the limitation of votes ?
        $mform->disabledIf('limitvotes', 'votes', '0');
        $mform->addElement('advcheckbox', 'limitvotes', get_string('limitvotes', 'stickynotes'));
        $mform->addHelpButton('limitvotes', 'limitvotes', 'stickynotes');

        // Max number of votes.
        $mform->disabledIf('maxlimitvotes', 'votes', '0');
        $mform->disabledIf('maxlimitvotes', 'limitvotes', '1');
        $maxvoteschoice = array(
            1   => 1,
            2   => 2,
            3   => 3,
            4   => 4,
            5   => 5,
            6   => 6,
            7   => 7,
            8   => 8,
            9   => 9,
            10  => 10,
        );
        $mform->addElement('select', 'maxlimitvotes', get_string('maxlimitvotes', 'stickynotes'), $maxvoteschoice);
        $mform->addHelpButton('maxlimitvotes', 'maxlimitvotes', 'stickynotes');
        $mform->setType('maxlimitvotes', PARAM_INT);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
