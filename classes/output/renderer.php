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

// Standard GPL and phpdocs.
namespace mod_stickynotes\output;

defined('MOODLE_INTERNAL') || die;

use mod_stickynotes;

 /**
  * Renderer outputting the stickynotes interface.
  *
  */
class renderer extends \plugin_renderer_base {
    /**
     * Renderer for sticky notes wall.
     *
     * @param index_page $notes
     * @return string html for the page
     */
    public function render_notes_list($notes) {
        return parent::render_from_template('mod_stickynotes/notes_list', $notes);
    }
}
