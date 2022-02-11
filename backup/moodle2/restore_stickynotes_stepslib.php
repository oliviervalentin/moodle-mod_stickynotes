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
 * Define all the restore steps that will be used by the restore_stickynotes_activity_task
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one stickynotes activity
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_stickynotes_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore workflow.
     *
     * @return restore_path_element $structure
     * @throws base_step_exception
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('stickynotes', '/activity/stickynotes');

        if ($userinfo) {
            $paths[] = new restore_path_element('stickynotes_column',
                '/activity/stickynotes/stickynotes_columns/stickynotes_column');
            $paths[] = new restore_path_element('stickynotes_note',
                '/activity/stickynotes/stickynotes_columns/stickynotes_column/stickynotes_notes/stickynotes_note');
            $paths[] = new restore_path_element('stickynotes_vote',
                '/activity/stickynotes/stickynotes_columns/stickynotes_column/stickynotes_notes/stickynotes_note/
                    stickynotes_votes/stickynotes_vote');
        }
        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a stickynotes restore.
     * @param object $data The data in object form
     * @return void
     */
    protected function process_stickynotes($data) {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        // Insert the page record.
        $newitemid = $DB->insert_record('stickynotes', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process stickynotes
     * @param object $data The data in object form
     * @return void
     * @throws dml_exception
     */
    protected function process_stickynotes_column($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->stickyid = $this->get_new_parentid('stickynotes');

        $newitemid = $DB->insert_record('stickynotes_column', $data);
        $this->set_mapping('stickynotes_column', $oldid, $newitemid);
    }
    /**
     * Process stickynotes_note
     * @param object $data The data in object form
     * @return void
     * @throws dml_exception
     */
    protected function process_stickynotes_note($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->stickyid = $this->get_new_parentid('stickynotes');
        $data->stickycolid = $this->get_new_parentid('stickynotes_column');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('stickynotes_note', $data);
        $this->set_mapping('stickynotes_note', $oldid, $newitemid);
    }
    /**
     * Process stickynotes_note
     * @param object $data The data in object form
     * @return void
     * @throws dml_exception
     */
    protected function process_stickynotes_vote($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->stickyid = $this->get_new_parentid('stickynotes');
        $data->stickynoteid = $this->get_new_parentid('stickynotes_note');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('stickynotes_vote', $data);
        $this->set_mapping('stickynotes_vote', $oldid, $newitemid);
    }
    /**
     * Post-execution actions
     */
    protected function after_execute() {
        // Add page related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_stickynotes', 'intro', null);
    }
}
