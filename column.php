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


// Declare optional parameters.
$edit = optional_param('edit', 0, PARAM_INT);
$create = optional_param('create', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$col = optional_param('col', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

// These params will be passed as hidden variables later in the form.
$pageparams = ['edit' => $edit, 'create' => $create];

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

// Get the system context instance.
$systemcontext = context_system::instance();

if ($id) {
    $cm = get_coursemodule_from_id('stickynotes', $id, 0, false, MUST_EXIST);
    $moduleinstance = $DB->get_record('stickynotes', ['id' => $cm->instance], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
}

$PAGE->set_url('/mod/stickynnotes/column.php', [
    'edit'           => $edit,
    'create'         => $create,
]);

require_login(0, false);

if (!empty($create)) {
    // Case 1 : manager wants to create a new column.
    // Retrieve the related coursemodule.
    if (!$cm = get_coursemodule_from_instance('stickynotes', $cm->instance)) {
        throw new moodle_exception('invalidcoursemodule');
    }
    // Check if the instance is part of a course.
    if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
        throw new moodle_exception('invalidcourseid');
    }

    $post = new stdClass();
    $post->title = '';
    $stickyid = $cm->instance;
} else if ($edit) {
    // Case 2 : manager wants updates column.
    // Retrieve the related coursemodule.
    if (!$cm = get_coursemodule_from_instance('stickynotes', $cm->instance)) {
        throw new moodle_exception('invalidcoursemodule');
    }

    // Check if the instance is part of a course.
    if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
        throw new moodle_exception('invalidcourseid');
    }

    if (!$post = $DB->get_record('stickynotes_column', ['id' => $col])) {
        throw new moodle_exception('cannotgetcolumn', 'stickynotes');
    }

    // Require a login and retrieve the modulecontext.
    require_login($course, false, $cm);
    $modulecontext = context_module::instance($cm->id);

    // Check capability.
    if (!has_capability('mod/stickynotes:managecolumn', $modulecontext)) {
        throw new moodle_exception('cannotmanagecolumn', 'stickynotes');
    }

    $post->edit = $edit;
    $post->course = $course->id;
    $post->title = $post->title;
    $post->col = $col;
} else if ($delete) {
    // Case 3 : manager wants to delete column.

    // Retrieve the related coursemodule.
    if (!$cm = get_coursemodule_from_instance('stickynotes', $cm->instance)) {
        throw new moodle_exception('invalidcoursemodule');
    }

    // Check if the instance is part of a course.
    if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
        throw new moodle_exception('invalidcourseid');
    }

    // Check if column exists.
    if (!$post = $DB->get_record('stickynotes_column', ['id' => $col])) {
        throw new moodle_exception('cannotgetcolumn', 'stickynotes');
    }
    // Require a login and retrieve the modulecontext.
    require_login($course, false, $cm);
    $modulecontext = context_module::instance($cm->id);

    // Check capability.
    if (!has_capability('mod/stickynotes:managecolumn', $modulecontext)) {
        throw new moodle_exception('cannotmanagecolumn', 'stickynotes');
    }

    // User has confirmed deletion : column is deleted.
    if (!empty($confirm) && confirm_sesskey()) {
        // First, retrieve all notes in selected column.
        $notestodelete = $DB->get_records('stickynotes_note', ['stickyid' => $cm->instance, 'stickycolid' => $col], '', '*');

        // Then, get infos about column to be deleted.
        $coltodelete = $DB->get_record('stickynotes_column', ['id' => $col]);

        // Third, retrieve all columns for which order rank is superior at selected column.
        $sql = "SELECT *
                FROM {stickynotes_column} snc
                WHERE stickyid = :instanceid AND column_order > :delcolorder";
        $params          = ['instanceid' => $cm->instance,
                            'delcolorder' => $coltodelete->column_order,
                        ];
        $colstochange = $DB->get_records_sql($sql, $params);

        // Update all superior columns : decrease rank of each superior columns by 1.
        foreach ($colstochange as $coltochange) {
            $coltochange->col = $coltochange->id;
            $oldorder = $coltochange->column_order;
            $coltochange->column_order = $oldorder - 1;
            update_column($coltochange);
        }

        // Then, every note in selected column is deleted.
        foreach ($notestodelete as $notetodelete) {
            $deletenote = delete_stickynote($notetodelete->id, $modulecontext, $moduleinstance,
            $course, $cm, $notetodelete->userid);
        }
        // Finally, delete column.
        $deletecolumn = delete_column($col, $modulecontext);

        $returnurl = "view.php?id=".$cm->id;
        redirect($returnurl);
    } else {
        // Shows form to confirm before delete.
        $modulecontext = context_module::instance($cm->id);
        $coursecontext = context_course::instance($course->id);
        $PAGE->navbar->add(get_string('deletecolumn', 'stickynotes'));
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);

        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($cm->name), 2);
        echo $OUTPUT->confirm(get_string("deletecolumnsure", "stickynotes"),
                "column.php?delete=$delete&confirm=$delete&id=".$cm->id."&col=".$col,
                $CFG->wwwroot . '/mod/stickynotes/view.php?id=' . $cm->id);
    }

    echo $OUTPUT->footer();
    exit;
}

// Prepare the form.
// Second step: The user must be logged on properly. Must be enrolled to the course as well.
require_login($course, false, $cm);

$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

// Get column infos.
$postcol = empty($post->col) ? null : $post->col;
$posttitle = empty($post->title) ? null : $post->title;

$formarray = [
    'id'             => $cm->id,
    'course'         => $course,
    'cm'             => $cm,
    'modulecontext'  => $modulecontext,
    'edit'           => $edit,
    'create'         => $create,
    'title'          => $posttitle,
    'col'            => $postcol,
    'stickyid'       => $cm->instance,
];

$mformcol = new form_column('column.php', $formarray, 'post');

$mformcol->set_data([
        'stickycolid' => $postcol,
        'id' => $id,
        'col' => $postcol,
    ] + $pageparams + $formarray);

// Is it canceled?
if ($mformcol->is_cancelled()) {
    $returnurl = "view.php?id=".$cm->id;
    redirect($returnurl);
}

// Is it submitted?
if ($fromform = $mformcol->get_data()) {
    // Redirect url in case of occuring errors.
    if (empty($SESSION->fromurl)) {
        $errordestination = "$CFG->wwwroot/mod/stickynotes/view.php?id=$cm->id";
    } else {
        $errordestination = $SESSION->fromurl;
    }

    // If we are updating column.
    if ($fromform->edit) {
        $fromform->userid = $USER->id;
        $fromform->instance = $fromform->id;
        $returnurl = "view.php?id=".$fromform->instance;
        update_column($fromform);
        redirect($returnurl);

    } else if ($fromform->create) {
        // Add a new column.
        $fromform->userid = $USER->id;
        $returnurl = "view.php?id=".$fromform->id;
        insert_column($fromform);
        redirect($returnurl);
        exit();
    }
}

// Initiate the page.
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading($cm->name);

$mformcol->display();

echo $OUTPUT->footer();
