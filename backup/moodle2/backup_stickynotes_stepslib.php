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
 * Define all the backup steps that will be used by the backup_stickynotes_activity_task.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete stickynotes structure for backup, with file and id annotations
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_stickynotes_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the root element describing the stickynotes instance.
        $stickynotes = new backup_nested_element('stickynotes', array('id'),
                                              array('course',
                                                    'name',
                                                    'intro',
                                                    'introformat',
                                                    'colors',
                                                    'votes',
                                                    'limitvotes',
                                                    'maxlimitvotes',
                                                    'limitstickynotes',
                                                    'maxstickynotes',
                                                    'orderstickynotes',
                                                    'viewauthor',
                                                    'timecreated',
                                                    'timemodified'));

        // Define each element separated.
        $stickynotescolumns = new backup_nested_element('stickynotescolumns');
        $stickynotescolumn = new backup_nested_element('stickynotescolumn', array('id'),
                                                            array('title',
                                                            'column_order'));

        $stickynotesnotes = new backup_nested_element('stickynotesnotes');
        $stickynotesnote = new backup_nested_element('stickynotesnote', array('id'),
                                                        array('userid',
                                                            'stickycolid',
                                                            'message',
                                                            'color',
                                                            'timecreated',
                                                            'timemodified'));
        $stickynotesvotes = new backup_nested_element('stickynotesvotes');
        $stickynotesvote = new backup_nested_element('stickynotesvote', array('id'),
                                                        array('userid',
                                                            'stickynoteid',
                                                            'vote',
                                                            'timecreated'));
        // Build the tree.
        $stickynotes->add_child($stickynotescolumns);
        $stickynotescolumns->add_child($stickynotescolumn);

        $stickynotescolumn->add_child($stickynotesnotes);
        $stickynotesnotes->add_child($stickynotesnote);

        $stickynotesnote->add_child($stickynotesvotes);
        $stickynotesvotes->add_child($stickynotesvote);

        $stickynotes->set_source_table('stickynotes', array('id' => backup::VAR_ACTIVITYID));

        $stickynotescolumn->set_source_sql('
                SELECT *
                FROM {stickynotes_column}
                WHERE stickyid = ?',
            array(backup::VAR_PARENTID));
        $stickynotesnote->set_source_sql('
                SELECT *
                FROM {stickynotes_note}
                WHERE stickyid = ?',
            array(backup::VAR_PARENTID));
        $stickynotescolumn->set_source_table('stickynotes_column', array('stickyid' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info.
        if ($this->get_task()->get_setting_value('userinfo')) {
            // All the rest of elements only happen if we are including user info.
            $stickynotesnote->set_source_table('stickynotes_note', array('stickycolid' => backup::VAR_PARENTID));
            $stickynotesvote->set_source_table('stickynotes_vote', array('stickynoteid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $stickynotesnote->annotate_ids('user', 'userid');
        $stickynotesvote->annotate_ids('user', 'userid');

        // Define file annotations.
        $stickynotes->annotate_files('mod_stickynotes', 'intro', null); // This file areas haven't itemid.

        // Return the root element (stickynotes), wrapped into standard activity structure.
        return $this->prepare_activity_structure($stickynotes);
    }
}
