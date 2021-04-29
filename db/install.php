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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     mod_stickynotes
 * @category    upgrade
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_stickynotes_install() {
    global $DB;

    $record1 = new stdClass();
    $record1->color = '#EECC66';
    $record2 = new stdClass();
    $record2->color = '#AACC24';
    $record3 = new stdClass();
    $record3->color = '#99DDFF';
    $record4 = new stdClass();
    $record4->color = '#6699CC';
    $record5 = new stdClass();
    $record5->color = '#EE8866';
    $record6 = new stdClass();
    $record6->color = '#BBBBBB';

    $records = array($record1, $record2, $record3, $record4, $record5, $record6);
    $DB->insert_records('stickynotes_colors', $records);

    return true;
}
