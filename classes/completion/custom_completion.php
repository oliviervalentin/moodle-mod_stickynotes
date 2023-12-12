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
 * Activity custom completion subclass for the stickynotes activity.
 *
 * @package     mod_stickynotes
 * @category    string
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_stickynotes\completion;

use core_completion\activity_custom_completion;

class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $stickyid = $this->cm->instance;

        if (!$stickynotes = $DB->get_record('stickynotes', ['id' => $stickyid])) {
            throw new \moodle_exception('Unable to find stickynotes activity with id ' . $stickyid);
        }

        $params = ['userid' => $userid, 'stickyid' => $stickyid];
        $sql = "SELECT COUNT(*)
                           FROM {stickynotes_note} sn
                           JOIN {stickynotes_column} sc ON sn.stickycolid = sc.id
                          WHERE sn.userid = :userid
                            AND sc.stickyid = :stickyid";

        if ($rule == 'completionstickynotes') {
            $machin = $DB->count_records_sql($sql, $params);
            $status = $stickynotes->completionstickynotes <= $DB->count_records_sql($sql, $params);
        }
// print_object("nombre de notes ".$machin);
// echo "<br/>";
// print_object("statut ".$status);
// exit();
        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionstickynotes',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $completionstickynotes = $this->cm->customdata['customcompletionrules']['completionstickynotes'] ?? 0;

        return [
            'completionstickynotes' => get_string('completionstickynotesdetail:notes', 'mod_stickynotes', $completionstickynotes)
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionstickynotes'
        ];
    }
}

