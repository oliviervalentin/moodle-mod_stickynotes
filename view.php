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
 * Prints an instance of mod_stickynotes.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/../../lib/outputcomponents.php');
global $DB, $USER;

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

$vote = optional_param('vote', 0, PARAM_INT);
$note = optional_param('note', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_RAW);

if ($id) {
    $cm = get_coursemodule_from_id('stickynotes', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('stickynotes', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($s) {
    $moduleinstance = $DB->get_record('stickynotes', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('stickynotes', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_stickynotes'));
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/stickynotes:view', $modulecontext);

$event = \mod_stickynotes\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('stickynotes', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/stickynotes/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->requires->js_call_amd('mod_stickynotes/dragndrop', 'init');

// Define some capabilities.
$capabilitycreatenote = false;
$capabilityvote = false;
$capabilityupdatenote = false;
$capabilitydeleteanynote = false;
if ((!is_guest($modulecontext, $USER) && isloggedin()) && has_capability('mod/stickynotes:vote', $modulecontext)
        && has_capability('mod/stickynotes:createnote', $modulecontext)) {
    $capabilitycreatenote = true;
    $capabilityvote = true;
}

// If user has just voted, first check capability.
if ($vote && !$capabilityvote) {
    print_error('cannotvote', 'stickynotes');
} else if ($vote && $capabilityvote) {
    // If vote limitation, first check if user is at max.
    if ($moduleinstance->limitvotes == 1) {
        $check = $DB->count_records('stickynotes_vote', array ('userid' => $USER->id, 'stickyid' => $cm->instance));
        if ($check >= $moduleinstance->maxlimitvotes && $action == 'add') {
            print_error('cannotvotelimitreached', 'stickynotes');
        }
    }
    // Call vote function for the Like vote type.
    if ($moduleinstance->votes == 1) {
        $dovote = stickynote_do_vote_like($USER->id, $note, $action, $cm->instance);
    }
    redirect("view.php?id=".$cm->id);
}

echo $OUTPUT->header();

echo $OUTPUT->heading($moduleinstance->name);

// Start to retrieve all columns for this instance.
$cols = $DB->get_records('stickynotes_column', array('stickyid' => $moduleinstance->id), '', '*');
$allcols = array();

// For each columns, retrieve all notes.
foreach ($cols as $col) {
    $notes = $DB->get_records('stickynotes_note', array('stickyid' => $moduleinstance->id, 'stickycolid' => $col->id),
    'ordernote', '*');

    $allnotes = new StdClass;
    $allnotes = array();

    // For each note, retrieve and define all necessary information.
    foreach ($notes as $note) {
        // Retieve author of the note.
        $getname = $DB->get_record('user', array('id' => $note->userid));
        $note->fullname = $getname->lastname." ".$getname->firstname;
        // Count number of votes for this note.
        $note->totalvotes = stickynote_count_votes($note->id);

        // If vote is enabled, check if user has voted for this note.
        if ($moduleinstance->votes == '1') {
            $getvote = stickynote_get_vote_like($USER->id, $note->id, $moduleinstance->limitvotes,
                $moduleinstance->maxlimitvotes, $cm->instance);
            $note->myvote = $getvote['myvote'];
            // If max number of votes per user is reached, limit vote.
            if ($moduleinstance->limitvotes == 1) {
                $note->limitedvote = $getvote['limitedvote'];
            }
            $note->action = $getvote['action'];
            $note->voteurl = $CFG->wwwroot.'/mod/stickynotes/view.php?id='.$cm->id.'&vote=1&action='.$note->action.'
&note='.$note->id;
        }

        // Defines elements for CSS.
        $note->elementid = 'element'.$note->id;

        // Check if rotate is enabled to apply the style with or without notes random rotation.
        if ($moduleinstance->rotate == 1) {
            $note->elementidcontainer = 'element'.$note->id.'container';
        } else {
            $note->elementidcontainer = 'element'.$note->id.'normal';
        }
        // Get background color from plugin settings.
        $getcolor = get_config('mod_stickynotes', $note->color);
        $note->backgroundcolor = $getcolor;

        // Define capabilities for edit and delete note.
        if ((!has_capability('mod/stickynotes:updateanynote', $modulecontext))
            && (!has_capability('mod/stickynotes:deleteanynote', $modulecontext))) {
            if ($note->userid == $USER->id) {
                if (has_capability('mod/stickynotes:updateownnote', $modulecontext)) {
                    $note->capabilityupdatenote = true;
                } else {
                    $note->capabilityupdatenote = false;
                }

                if (has_capability('mod/stickynotes:deleteownnote', $modulecontext)) {
                    $note->capability_deletenote = true;
                } else {
                    $note->capability_deletenote = false;
                }
            }
        } else {
            // If admin : all edit capabilities are ok.
            $note->capabilityupdatenote = true;
            $note->capability_deletenote = true;
        }

        // Define URLs for edit and delete.
        $note->editnoteurl = $CFG->wwwroot . '/mod/stickynotes/note.php?id='.$id.'&edit=1&note=' . $note->id;
        $note->deletenoteurl = $CFG->wwwroot . '/mod/stickynotes/note.php?id='.$id.'&delete=1&note=' . $note->id;

        $allnotes[] = (array)$note;
    }
    $notescol = new StdClass;
    $notescol->columnid = $col->id;
    $notescol->allnotes = $allnotes;
    // Create URL to create note.
    $notescol->createnoteurl = $CFG->wwwroot . '/mod/stickynotes/note.php?id='.$id.'&create=1&col=' . $col->id;
    $notescol->title = $col->title;

    // Create urls to manage columns.
    $notescol->editcolumnurl = $CFG->wwwroot . '/mod/stickynotes/column.php?id='.$id.'&edit=1&col=' . $col->id;
    $notescol->deletecolumnurl = $CFG->wwwroot . '/mod/stickynotes/column.php?id='.$id.'&delete=1&col=' . $col->id;
    $allcols[] = (array)$notescol;
}

// All informations are set.
$all = new StdClass;
$all->allcols = $allcols;

// Check capability for manage columns and define URL to create column.
if (has_capability('mod/stickynotes:managecolumn', $modulecontext)) {
    $all->capability_managecolumn = true;
    $all->createcolumnurl = $CFG->wwwroot . '/mod/stickynotes/column.php?id='.$id.'&create=1';
}
$all->capabilitycreatenote = $capabilitycreatenote;
$all->capabilityvote = $capabilityvote;

// Check capability for view author and if option is enabled.
if (has_capability('mod/stickynotes:viewauthor', $modulecontext) && $moduleinstance->viewauthor) {
    $all->capability_viewauthor = true;
}

// Set if vote is enabled.
if ($moduleinstance->votes == '0') {
    $all->voteenabled = 0;
} else {
    $all->voteenabled = 1;
}

// If max limit of notes per user is enabled, check if user has reached maximum.
if ($moduleinstance->limitstickynotes == 1) {
    $notescreated = stickynote_count_notes($USER->id, $cm->instance);
    if ($moduleinstance->maxstickynotes == $notescreated ) {
        $all->maxnotesreached = 1;
    }
}

$output = $PAGE->get_renderer('mod_stickynotes');

echo $output->render_notes_list($all);

echo $OUTPUT->footer();
