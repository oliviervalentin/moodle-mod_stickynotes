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
global $DB, $USER;

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

$vote = optional_param('vote', 0, PARAM_INT);
$note = optional_param('note', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_RAW);
$lock = optional_param('lock', 0, PARAM_RAW);
$lockvalue = optional_param('lockvalue', 0, PARAM_RAW);

if ($id) {
    $cm = get_coursemodule_from_id('stickynotes', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('stickynotes', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($s) {
    $moduleinstance = $DB->get_record('stickynotes', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('stickynotes', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception(get_string('missingidandcmid', 'mod_stickynotes'));
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/stickynotes:view', $modulecontext);

$event = \mod_stickynotes\event\course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('stickynotes', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/stickynotes/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$config = ['paths' => ['sortablejs' => $CFG->wwwroot .'/mod/stickynotes/js/sortable.min']];
$requirejs = 'require.config(' . json_encode($config) . ')';
$PAGE->requires->js_amd_inline($requirejs);

$PAGE->requires->js_call_amd('mod_stickynotes/dragndrop', 'init');
$PAGE->requires->js_call_amd('mod_stickynotes/refresh', 'init');

// Define some capabilities.
$capabilitycreatenote = false;
$capabilityvote = false;
$capabilityupdatenote = false;
$capabilitydeleteanynote = false;
$capabilitymoveallnotes = false;
$locknotes = false;
$lockvotes = false;

// Check capability for notes creation and votes. Two possibilities.
// 1) user is admin. He can always create or vote.
if ((has_capability('mod/stickynotes:updateanynote', $modulecontext))
    && (has_capability('mod/stickynotes:deleteanynote', $modulecontext))) {
    $capabilitycreatenote = true;
    $capabilityvote = true;
    $isadmin = 1;
} else if ((!is_guest($modulecontext, $USER) && isloggedin())
        && has_capability('mod/stickynotes:vote', $modulecontext)
        && has_capability('mod/stickynotes:createnote', $modulecontext)) {
    // 2) user is a logged student. Check if note creation and votes are not locked before giving capability.
    // Check notes lock.
    if ($moduleinstance->locknotes == 1) {
        $locknotes = true;
        $capabilitycreatenote = false;
    } else {
        $locknotes = false;
        $capabilitycreatenote = true;
    }
    // Check votes lock.
    if ($moduleinstance->lockvotes == 1) {
        $capabilityvote = false;
        $lockvotes = true;
    } else {
        $capabilityvote = true;
        $lockvotes = false;
    }
}

// If user has just voted, first check capability.
if ($vote && !$capabilityvote) {
    throw new moodle_exception('cannotvote', 'stickynotes');
} else if ($vote && $capabilityvote) {
    // Check if vote is locked.
    if ($moduleinstance->lockvotes == 1) {
        throw new moodle_exception('activelockvotes', 'stickynotes');
    }
    // If vote limitation, first check if user is at max.
    if ($moduleinstance->limitvotes == 1) {
        $check = $DB->count_records('stickynotes_vote', ['userid' => $USER->id, 'stickyid' => $cm->instance]);
        if ($check >= $moduleinstance->maxlimitvotes && $action == 'add') {
            throw new moodle_exception('cannotvotelimitreached', 'stickynotes');
        }
    }
    // Call vote function for the Like vote type.
    if ($moduleinstance->votes == 1) {
        $dovote = stickynote_do_vote_like($USER->id, $note, $action, $cm->instance);
    }
    redirect("view.php?id=".$cm->id);
}

// If admin locks votes or notes, first check capability.
if ($lock && $isadmin == 0) {
    throw new moodle_exception('activelock', 'stickynotes');
} else if ($lock && $isadmin == 1) {
    $updatelock = update_lock($cm->instance, $lock, $lockvalue);
    redirect("view.php?id=".$cm->id);
}

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

echo $OUTPUT->header();

// Start to retrieve all columns for this instance.
$cols = $DB->get_records('stickynotes_column', ['stickyid' => $moduleinstance->id], '', '*');
$allcols = [];

// For each columns, retrieve all notes.
foreach ($cols as $col) {
    // If user has supercapabilities, we show all notes.
    if ((has_capability('mod/stickynotes:updateanynote', $modulecontext))
            && (has_capability('mod/stickynotes:deleteanynote', $modulecontext))) {
                $notes = $DB->get_records('stickynotes_note', ['stickyid' => $moduleinstance->id, 'stickycolid' => $col->id],
                'ordernote', '*');
    } else {
        // If user hasn't capabilities, check if he can see all notes through activity parameters.
        if ($moduleinstance->seeallnotes == 1) {
            $notes = $DB->get_records('stickynotes_note', ['stickyid' => $moduleinstance->id, 'stickycolid' => $col->id],
            'ordernote', '*');
        } else {
            $notes = $DB->get_records('stickynotes_note', ['stickyid' => $moduleinstance->id, 'stickycolid' => $col->id,
            'userid' => $USER->id, ], 'ordernote', '*');
        }
    }

    $allnotes = new StdClass;
    $allnotes = [];

    // For each note, retrieve and define all necessary information.
    foreach ($notes as $note) {
        // Retrieve author of the note.
        $getname = $DB->get_record('user', ['id' => $note->userid]);
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
            $note->elementidstickycontent = 'element'.$note->id.'stickycontent';
        } else {
            $note->elementidstickycontent = 'element'.$note->id.'stickynormal';
        }
        // Get background color from plugin settings.
        $getcolor = get_config('mod_stickynotes', $note->color);
        $note->backgroundcolor = $getcolor;
        $note->textcolor = getcontrastcolor($getcolor);

        // Define capabilities for edit and delete note.
        // If user can't update and delete everything, it's not an admin. Must check capacities for each note.
        if ((!has_capability('mod/stickynotes:updateanynote', $modulecontext))
            && (!has_capability('mod/stickynotes:deleteanynote', $modulecontext))) {
            // First, check if setting to move all notes is enabled, to give or not this capability.
            if ($moduleinstance->moveallnotes == 1) {
                $note->capabilitymoveallnotes = true;
            } else {
                $note->capabilitymoveallnotes = false;
            }
            // Now, check if this note belongs to user to set update and delete capabilities.
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
$all->locknotes = $locknotes;
$all->lockvotes = $lockvotes;

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
    // If user can view author, it's a teacher ! No creation limit for teachers.
    if (has_capability('mod/stickynotes:viewauthor', $modulecontext)) {
        $all->maxnotesreached = 0;
    } else {
        $notescreated = stickynote_count_notes($USER->id, $cm->instance);
        if ($moduleinstance->maxstickynotes == $notescreated ) {
            $all->maxnotesreached = 1;
        }
    }
}

echo "<div id='descandcapt' style='margin-bottom: 1em'>";

// If enabled, display button to show legend.
if ($moduleinstance->displaystickycaption == '1') {
    echo '<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#displaycapt" aria-expanded="false" aria-controls="displaycapt">
    '.get_string('buttondisplaystickycaption', 'mod_stickynotes').'</button>';
}
if ((has_capability('mod/stickynotes:updateanynote', $modulecontext))
    && (has_capability('mod/stickynotes:deleteanynote', $modulecontext))) {
    echo "<div style='float: right; margin-bottom: 1em'>";
    if ($moduleinstance->locknotes == 0) {
        $url = $CFG->wwwroot.'/mod/stickynotes/view.php?id='.$cm->id.'&lock=locknotes&lockvalue=1';
        echo "<a href=".$url."><button class='btn btn-primary'><i class='fa fa-lock'></i> "
        .get_string('buttonlocknotes', 'mod_stickynotes')."</button></a>&nbsp;";
    } else {
        $url = $CFG->wwwroot.'/mod/stickynotes/view.php?id='.$cm->id.'&lock=locknotes&lockvalue=0';
        echo "<a href=".$url."><button class='btn btn-primary'><i class='fa fa-unlock'></i> "
        .get_string('buttonunlocknotes', 'mod_stickynotes')."</button>&nbsp;";
    }
    if ($moduleinstance->lockvotes == 0) {
        $url = $CFG->wwwroot.'/mod/stickynotes/view.php?id='.$cm->id.'&lock=lockvotes&lockvalue=1';
        echo "<a href=".$url."><button class='btn btn-primary'><i class='fa fa-lock'></i> "
        .get_string('buttonlockvotes', 'mod_stickynotes')."</button></a>";
    } else {
        $url = $CFG->wwwroot.'/mod/stickynotes/view.php?id='.$cm->id.'&lock=lockvotes&lockvalue=0';
        echo "<a href=".$url."><button class='btn btn-primary'><i class='fa fa-unlock'></i> "
        .get_string('buttonunlockvotes', 'mod_stickynotes')."</button></a>";
    }
    echo "</div>";
}
echo "</div>";

// This next div is for displaying isntructions and caption if necessary.
echo '<div style="margin-bottom: 3em;">';

// If enabled, display button to show legend.
if ($moduleinstance->displaystickycaption == '1') {
    // First, list the 6 colors.
    $configcolor = [
        'color1',
        'color2',
        'color3',
        'color4',
        'color5',
        'color6',
    ];
    // Second, retrieve colors settings for this instance.
    $retrievecolors = $DB->get_record('stickynotes', ['id' => $moduleinstance->id], '*', MUST_EXIST);

    $colorarray = [];
    echo '<div class="collapse" id="displaycapt"><div class="card card-body">';
    echo '<h3>'.get_string('titledisplaystickycaption', 'mod_stickynotes').'</h3>';
    foreach ($configcolor as $color) {
        if ($retrievecolors->$color == 1) {
            // If a color is used in instance, design a colored square and add meaning if define.
            $thiscolor = "<div><div style=\"display: inline-block;width:50px;margin-bottom:5px;margin-right:10px;background-color:"
            .get_config('mod_stickynotes', $color)
            ."\">&nbsp;</div>&nbsp;";
            $thiscolor .= "<div style=\"display: inline-block\">".$DB->get_field('stickynotes', $color.'_meaning',
            ['id' => $moduleinstance->id]);
            $thiscolor .= "<br /></div></div>";
            echo $thiscolor;
        }
    }
    echo '</div></div>';
}

$output = $PAGE->get_renderer('mod_stickynotes');

echo $output->render_notes_list($all);

echo $OUTPUT->footer();
