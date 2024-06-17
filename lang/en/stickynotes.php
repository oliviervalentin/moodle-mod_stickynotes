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
$string['modulenameplural'] = 'Sticky Notes activities';
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
$string['stickynotes:export'] = 'Export all notes';

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
$string['colors'] = 'Let user choose background color for notes';
$string['colors_help'] = 'If enabled, users can choose the background color for notes among the ones selected by teacher.';
$string['rotate'] = 'Rotate notes';
$string['rotate_help'] = 'If enabled, notes will have a random rotation effect.';
$string['choosecolors'] = 'Select which colors will be available for notes backgrounds.';
$string['color1_meaning'] = 'Signification for color 1';
$string['color2_meaning'] = 'Signification for color 2';
$string['color3_meaning'] = 'Signification for color 3';
$string['color4_meaning'] = 'Signification for color 4';
$string['color5_meaning'] = 'Signification for color 5';
$string['color6_meaning'] = 'Signification for color 6';
$string['settings_colors'] = 'Colors settings';
$string['settings_votes'] = 'Votes settings';
$string['settings_notes'] = 'Notes settings';
$string['displaystickycaption'] = 'Display colors caption in activity.';
$string['displaystickycaption_help'] = 'If enabled, adds a caption with colors and their meanings.';
$string['moveallnotes'] = 'Students can move any notes';
$string['moveallnotes_help'] = 'If enabled, students will be able to move all notes, and not only their owns. Though, students won\'t be able to modify content, color, delete note...';
$string['seeallnotes'] = 'Students see all notes allover activity.';
$string['seeallnotes_help'] = 'If enabled, students will see all notes in activity. Let it enabled for a cooperative work. Note that teacher always sees all notes.';
$string['completionstickynotesenabled'] = 'Students must create this number of sticky notes to complete the activity.';
$string['completionstickynotesgroup'] = 'Require notes';
$string['completionstickynotesdetail:notes'] = 'Add sticky notes: {$a}';

// Colors settings.
$string['color1'] = '#EECC66';
$string['color1_descr'] = 'Color code for Color 1. Color 1 is also default color if color choice is not enabled in activity.';
$string['color1_title'] = 'Color 1';
$string['color2'] = '#AACC24';
$string['color2_descr'] = 'Color code for Color 2.';
$string['color2_title'] = 'Color 2';
$string['color3'] = '#99DDFF';
$string['color3_descr'] = 'Color code for Color 3.';
$string['color3_title'] = 'Color 3';
$string['color4'] = '#6699CC';
$string['color4_descr'] = 'Color code for Color 4.';
$string['color4_title'] = 'Color 4';
$string['color5'] = '#EE8866';
$string['color5_descr'] = 'Color code for Color 5.';
$string['color5_title'] = 'Color 5';
$string['color6'] = '#BBBBBB';
$string['color6_descr'] = 'Color code for Color 6.';
$string['color6_title'] = 'Color 6';

// Forms strings.
$string['message'] = 'Message';
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
$string['createnote_title'] = 'Create a new note in column ';
$string['updatenote_title'] = 'Update note';
$string['choosecolorbuttons'] = 'Choose background color';
$string['after'] = 'After';
$string['firstplace'] = 'First place in column';
$string['lastplace'] = 'At the end of column';
$string['nomove'] = 'Move this note ?';
$string['nomove_help'] = 'If checked, enables the selection menus to move note in another rank and/or column.';
$string['selectorder'] = 'Order';
$string['activelock'] = 'Notes and/or votes are locked';
$string['activelocknotes'] = 'Notes creation is locked';
$string['activelockvotes'] = 'Vote is locked';

// Mustache template strings.
$string['createnote'] = 'Add new Sticky Note';
$string['editnote'] = 'Edit this Sticky Note';
$string['createcolumn'] = 'Add new column';
$string['titledisplaystickycaption'] = 'CAPTION';
$string['buttondisplaystickycaption'] = 'Display color caption';
$string['buttonlocknotes'] = 'Lock notes';
$string['buttonlockvotes'] = 'Lock votes';
$string['buttonunlocknotes'] = 'Unlock notes';
$string['buttonunlockvotes'] = 'Unlock votes';

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
$string['move_cross_pix'] = "Drag and drop this note";
$string['createnotelocked'] = "Note creation locked";
$string['votelockedpix'] = 'Vote locked';

// Events.
$string['eventnotecreated'] = 'Sticky note created';
$string['eventnoteupdated'] = 'Sticky note updated';
$string['eventnotedeleted'] = 'Sticky note deleted';

// Navigation.
$string['export'] = 'Export notes in CSV';

// Reset functions.
$string['resetstickynotesall'] = 'Reset all activity (notes and columns)';
$string['resetstickynotesnotes'] = 'Reset notes and votes only';
$string['resetstickynotesvotes'] = 'Reset votes only';
$string['removeallresponse'] = 'Sticky contents deleted';
$string['removenotesandvotesresponse'] = 'Notes and votes deleted';
$string['removevotesresponse'] = 'Votes deleted';

// Privacy.
$string['privacy:metadata:stickynotes_note'] = 'Datas for user notes';
$string['privacy:metadata:stickynotes_note:id'] = 'Note ID';
$string['privacy:metadata:stickynotes_note:stickyid'] = 'Module ID';
$string['privacy:metadata:stickynotes_note:stickycolid'] = 'Column ID in activity';
$string['privacy:metadata:stickynotes_note:userid'] = 'User ID that has created this note';
$string['privacy:metadata:stickynotes_note:message'] = 'Content of note';
$string['privacy:metadata:stickynotes_note:timecreated'] = 'The time when the note was created';
$string['privacy:metadata:stickynotes_note:timemodified'] = 'The time when the note was updated';
$string['privacy:metadata:stickynotes_vote'] = 'Datas for user votes';
$string['privacy:metadata:stickynotes_vote:id'] = 'Vote ID';
$string['privacy:metadata:stickynotes_vote:stickyid'] = 'Module ID';
$string['privacy:metadata:stickynotes_vote:stickynoteid'] = 'Note ID user has voted for';
$string['privacy:metadata:stickynotes_vote:userid'] = 'User ID that has voted for this note';
$string['privacy:metadata:stickynotes_vote:vote'] = 'Vote content';
$string['privacy:metadata:stickynotes_vote:timecreated'] = 'The time when user has voted';
