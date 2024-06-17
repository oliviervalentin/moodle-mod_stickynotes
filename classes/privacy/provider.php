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
 * Privacy class for requesting user data.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_stickynotes\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the Stickynotes activity module.
 */
class provider implements
    // This plugin stores personal data.
    \core_privacy\local\metadata\provider,

    // This plugin is a core_user_data_provider.
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items): collection {
        // 1. The 'stickynotes_note' table stores information about notes created by user.
        $items->add_database_table(
            'stickynotes_note',
            [
                'id' => 'privacy:metadata:stickynotes_note:id',
                'stickyid' => 'privacy:metadata:stickynotes_note:stickyid',
                'stickycolid' => 'privacy:metadata:stickynotes_note:stickycolid',
                'userid' => 'privacy:metadata:stickynotes_note:userid',
                'message' => 'privacy:metadata:stickynotes_note:message',
                'timecreated' => 'privacy:metadata:stickynotes_note:timecreated',
                'timemodified' => 'privacy:metadata:stickynotes_note:timemodified',
            ],
            'privacy:metadata:stickynotes_note'
        );
        // 2. The 'stickynotes_vote' table stores information about which notes a user has rated.
        $items->add_database_table(
            'stickynotes_vote',
            [
                'id' => 'privacy:metadata:stickynotes_vote:id',
                'stickyid' => 'privacy:metadata:stickynotes_vote:stickyid',
                'stickynoteid' => 'privacy:metadata:stickynotes_vote:stickynoteid',
                'userid' => 'privacy:metadata:stickynotes_vote:userid',
                'vote' => 'privacy:metadata:stickynotes_vote:vote',
                'timecreated' => 'privacy:metadata:stickynotes_vote:timecreated',
            ],
            'privacy:metadata:stickynotes_vote'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * In the case of forum, that is any forum where the user has made any post, rated any content, or has any preferences.
     *
     * @param   int         $userid     The user to search.
     * @return  contextlist $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : \core_privacy\local\request\contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $params = [
            'modname'       => 'stickynotes',
            'contextlevel'  => CONTEXT_MODULE,
            'userid'        => $userid,
        ];

        // Stickynotes notes.
        $sql = "SELECT c.id
            FROM {context} c
            JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            JOIN {stickynotes} s ON s.id = cm.instance
            JOIN {stickynotes_column} sc ON sc.stickyid = s.id
            JOIN {stickynotes_note} sn ON sn.stickycolid = sc.id
            WHERE sn.userid = :userid
        ";
        $contextlist->add_from_sql($sql, $params);

        // Stickynotes votes.
        $sql = "SELECT c.id
            FROM {context} c
            JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            JOIN {stickynotes} s ON s.id = cm.instance
            JOIN {stickynotes_column} sc ON sc.stickyid = s.id
            JOIN {stickynotes_note} sn ON sn.stickycolid = sc.id
            JOIN {stickynotes_vote} sv ON sv.stickynoteid = sn.id
            WHERE sv.userid = :userid
        ";
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        $params = [
            'instanceid'    => $context->instanceid,
            'modname'    => 'stickynotes',
        ];

        // Notes authors.
        $sql = "SELECT sn.userid
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            JOIN {stickynotes} s ON s.id = cm.instance
            JOIN {stickynotes_column} sc ON sc.stickyid = s.id
            JOIN {stickynotes_note} sn ON sn.stickycolid = sc.id
            WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Votes.
        $sql = "SELECT sv.userid
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            JOIN {stickynotes} s ON s.id = cm.instance
            JOIN {stickynotes_column} sc ON sc.stickyid = s.id
            JOIN {stickynotes_note} sn ON sn.stickycolid = sc.id
            JOIN {stickynotes_vote} sv ON sv.stickynoteid = sn.id
            WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB, $CFG;

        $userid = $contextlist->get_user()->id;

        if (empty($contextlist)) {
            return;
        }
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT
                    c.id AS contextid,
                    s.id,
                    cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {stickynotes} s ON s.id = cm.instance
                 WHERE (
                    c.id {$contextsql}
                )
        ";
        // Keep a mapping of stickyid to contextid.
        $mappings = [];

        $params = ['modname' => 'stickynotes', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;
        $stickynotesactivities = $DB->get_recordset_sql($sql, $params);

        foreach ($stickynotesactivities as $stickynotes) {
            $mappings[$stickynotes->id] = $stickynotes->contextid;

            $context = \context::instance_by_id($mappings[$stickynotes->id]);
            $subcontext = [
                get_string('pluginname', 'mod_stickynotes'),
                format_string($stickynotes->name),
                $stickynotes->id,
            ];

            // Get all notes created by the user.
            $sql1notes = "SELECT id, message, timecreated, timemodified
            from {stickynotes_note}
            where userid = :userid
            and stickyid = :stickynotesid";

            $query1notes = $DB->get_records_sql($sql1notes, ['userid' => $userid, 'stickynotesid' => $stickynotes->id]);
            foreach ($query1notes as $query1note) {
                $key = $query1note->id;
                $notedata[$key] = (object) [
                    'id' => $query1note->id,
                    'message' => format_string($query1note->message, true),
                    'timecreated' => transform::datetime($query1note->timecreated),
                    'timemodified' => transform::datetime($query1note->timemodified),
                ];
            }

            // Get all votes of this user.
            $sql2notes = "SELECT sv.id as idvote, sv.vote, sv.timecreated, sn.id as idnote
            from {stickynotes_note} sn
            join {stickynotes_vote} sv on sv.stickynoteid = sn.id
            where sv.userid = :userid
            and sv.stickyid = :stickynotesid";

            $query2notes = $DB->get_records_sql($sql2notes, ['userid' => $userid, 'stickynotesid' => $stickynotes->id]);
            foreach ($query2notes as $query2note) {
                $key = $query2note->idnote;
                $votedata[$key] = (object) [
                    'idnote' => $query2note->idnote,
                    'vote' => $query2note->vote,
                    'timecreated' => transform::datetime($query2note->timecreated),
                ];
            }

            $stickynotes->notes = $notedata;
            $stickynotes->votes = $votedata;
            unset($notedata);
            unset($votedata);

            writer::with_context($context)->export_data([], $stickynotes);
        }
        $stickynotesactivities->close();
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('stickynotes', $context->instanceid)) {
            $DB->delete_records('stickynotes_note', ['stickyid' => $cm->instance]);
            $DB->delete_records('stickynotes_vote', ['stickyid' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {

            if (!$context instanceof \context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid]);
            if (!$instanceid) {
                continue;
            }
            $DB->delete_records('stickynotes_note', ['stickyid' => $instanceid, 'userid' => $userid]);
            $DB->delete_records('stickynotes_vote', ['stickyid' => $instanceid, 'userid' => $userid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('stickynotes', $context->instanceid);

        if (!$cm) {
            // Only stickynotes module will be handled.
            return;
        }

        $userids = $userlist->get_userids();
        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $select = "stickyid = :stickyid AND userid $usersql";
        $params = ['stickyid' => $cm->instance] + $userparams;
        $DB->delete_records_select('stickynotes_note', $select, $params);
        $DB->delete_records_select('stickynotes_note', $select, $params);
    }
}
