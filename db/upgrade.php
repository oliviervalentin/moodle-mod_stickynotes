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
 * Library of interface functions and constants.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_stickynotes upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_stickynotes_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2021051002) {
        $table = new xmldb_table('stickynotes_note');
        $field = $table->add_field('ordernote', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $ids = $DB->get_fieldset_sql('SELECT sn.id FROM {stickynotes} sn WHERE sn.id > 0');
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $cols = $DB->get_records('stickynotes_column', array('stickyid' => $id), '', '*');
                foreach ($cols as $col) {
                    $notes = $DB->get_records('stickynotes_note', array('stickycolid' => $col->id), '', '*');
                    $i = 0;
                    foreach ($notes as $note) {
                        $obj = new stdClass();
                        $obj->id = $note->id;
                        $obj->ordernote = $i + 1;
                        $DB->update_record('stickynotes_note', $obj);
                        $i = $obj->ordernote;
                    }
                }
            }
        }
        
        // Sticky notes savepoint reached.
        upgrade_mod_savepoint(true, 2021051002, 'stickynotes');
    }
    if ($oldversion < 2021110403) {
        $table = new xmldb_table('stickynotes');
        $field1 = $table->add_field('displaystickydesc', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $field2 = $table->add_field('displaystickycaption', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        
        // Sticky notes savepoint reached.
        upgrade_mod_savepoint(true, 2021110403, 'stickynotes');
    }
    
    return true;
}
