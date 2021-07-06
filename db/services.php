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
 * Web service for mod stickynotes
 * @package    mod_stickynotes
 * @subpackage db
 * @copyright  2021 Sébastien Mehr <sebmehr.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

        'mod_stickynotes_changing_note_position' => array(
                'classname'     => 'mod_stickynotes_external',
                'methodname'    => 'changing_note_position',
                'classpath'     => 'mod/stickynotes/externallib.php',
                'description'   => 'Changing note position via ajax',
                'type'          => 'write',
                'ajax'          => true,
                'capabilities'  => 'mod/stickynotes:updateownnote'
        ),

         'mod_stickynotes_get_notes_column_select' => array(
                'classname'   => 'mod_stickynotes_external',
                'methodname'  => 'get_notes_column_select',
                'classpath'   => 'mod/stickynotes/externallib.php',
                'description' => 'Return notes un a column for select menu',
                'type'        => 'read',
                'ajax' => true,
                'loginrequired' => true
          )
);