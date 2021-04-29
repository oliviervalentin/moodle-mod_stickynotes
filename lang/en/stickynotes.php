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
 * Plugin strings are defined here.
 *
 * @package     mod_stickynotes
 * @category    string
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Sticky Notes';
$string['modulename'] = 'Sticky Notes';
$string['pluginadministration'] = 'Sticky Notes Admin';
$string['modulenameplural'] = 'Sticky Notes';
$string['missingidandcmid'] = 'Missing parameters';
$string['new_column_title'] = 'Title 1';

// Access strings.
$string['stickynotes:addinstance'] = 'Add a new stickynotes instance';
$string['stickynotes:view'] = 'View stickynotes content';
$string['stickynotes:createnote'] = 'Create a note';
$string['stickynotes:updateanynote'] = 'Update any note';
$string['stickynotes:updateownnote'] = 'Update own note';
$string['stickynotes:deleteanynote'] = 'Delete any note';
$string['stickynotes:deleteownnote'] = 'Delete own note';
$string['stickynotes:managecolumn'] = 'Manage columns';
$string['stickynotes:vote'] = 'Vote for a note';
$string['stickynotes:viewauthor'] = 'View author for each note';

// Settings strings.
$string['stickynotesname'] = 'Activity name';
$string['vote'] = 'Votes';
$string['votetype'] = 'Vote type';
$string['votetype_help'] = 'Defines enabled type of vote';
$string['votenone'] = 'No votes';
$string['votelike'] = '"Like" Vote';
$string['limitstickynotes'] = 'Limit notes ?';
$string['limitstickynotes_help'] = 'If enabled, users will have a limit number of notes they can create.';
$string['maxstickynotes'] = 'Max notes per user';
$string['maxstickynoteserror'] = 'Error : must be a positive number different from 0.';
$string['limitvotes'] = 'Limit votes ?';
$string['limitvotes_help'] = 'If enabled, users will have a limit number of votes.';
$string['maxlimitvotes'] = 'Max votes number';
$string['maxlimitvotes_help'] = 'Defines the number of votes per user.';
$string['viewauthor'] = 'Show authors';
$string['viewauthor_help'] = 'If enabled, managers and teachers will see authors ont he top of each note.';
$string['colors'] = 'Colors choice';
$string['colors_help'] = 'If enabled, users can choose the background color for notes.';

// Forms strings.
$string['message'] = 'Type your message here';
$string['validate'] = 'Save';
$string['maximumchars'] = 'The maximul length for a message is limited to 100 characters';
$string['title'] = 'Column title';
$string['changecolumn'] = 'Move this note to ';
$string['choosecolor'] = 'Background color ';
$string['deletenote'] = 'Delete note';
$string['deletenotesure']  = 'Are you sure to delete this note ? ';
$string['deletecolumn'] = 'Delete column';
$string['deletecolumnsure']  = 'Are you sure to delete this column and all its content ? All notes will be definitely deleted.';
$string['cannotgetnote']  = 'This note doesn\'t exist in database.';
$string['cannotgetcolumn']  = 'This column doesn\'t exist in database.';
$string['cannotcreatenote']  = 'You are not authorized to create notes.';
$string['cannotupdatenote']  = 'You are not authorized to update this note.';
$string['cannotdeletenote']  = 'You are not authorized to delete this note.';
$string['cannotmanagecolumn']  = 'You are not authorized to manage columns';
$string['cannotvote']  = 'You are not authorized to vote';
$string['cannotvotelimitreached']  = 'Your max vote limit is reached';
$string['erroremptymessage'] = 'You must write a message for your note';
$string['erroremptytitle'] = 'You must give a title to your column';

// Mustache template strings.
$string['createnote'] = 'Add new Sticky Note';
$string['editnote'] = 'Edit this Sticky Note';
$string['createcolumn'] = 'Add new column';

// Pix in mustache template.
$string['heart_empty_pix'] = 'Add a Like';
$string['heart_full_pix'] = 'Remove your Like';
$string['heart_limited_pix'] = 'Vote limit reached';
$string['max_notes_reached_pix'] = 'Max notes reached : delete a note if you want to create another.';
$string['create_note_pix'] = 'Create new note in this column';
$string['create_column_pix'] = 'Create new column';
$string['edit_column_pix'] = 'Edit this column';
$string['delete_column_pix'] = 'Delete this column';
$string['edit_note_pix'] = 'Edit this note';
$string['delete_note_pix'] = 'Delete this note';
