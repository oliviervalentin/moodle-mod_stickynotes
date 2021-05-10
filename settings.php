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
 * Plugin administration pages are defined here.
 *
 * @package     mod_stickynotes
 * @category    admin
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    if ($ADMIN->fulltree) {
        // Define all background colors for notes. Default colors are choosen in order to respect
        // accessibilty, an particularly blindcolorness. User can define others colors for the whole site
        // to respect template colors.

        // Defines color 1. Color 1 is also default color for notes background if color choice is not enabled.
        $settings->add(new admin_setting_configcolourpicker('mod_stickynotes/color1',
        get_string('color1_title', 'mod_stickynotes'),
        get_string('color1_descr', 'mod_stickynotes'),
        get_string('color1', 'mod_stickynotes'),
        null )
        );

        // Defines color 2.
        $settings->add(new admin_setting_configcolourpicker('mod_stickynotes/color2',
        get_string('color2_title', 'mod_stickynotes'),
        get_string('color2_descr', 'mod_stickynotes'),
        get_string('color2', 'mod_stickynotes'),
        null )
        );

        // Defines color 3.
        $settings->add(new admin_setting_configcolourpicker('mod_stickynotes/color3',
        get_string('color3_title', 'mod_stickynotes'),
        get_string('color3_descr', 'mod_stickynotes'),
        get_string('color3', 'mod_stickynotes'),
        null )
        );

        // Defines color 4.
        $settings->add(new admin_setting_configcolourpicker('mod_stickynotes/color4',
        get_string('color4_title', 'mod_stickynotes'),
        get_string('color4_descr', 'mod_stickynotes'),
        get_string('color4', 'mod_stickynotes'),
        null )
        );

        // Defines color 5.
        $settings->add(new admin_setting_configcolourpicker('mod_stickynotes/color5',
        get_string('color5_title', 'mod_stickynotes'),
        get_string('color5_descr', 'mod_stickynotes'),
        get_string('color5', 'mod_stickynotes'),
        null )
        );

        // Defines color 6.
        $settings->add(new admin_setting_configcolourpicker('mod_stickynotes/color6',
        get_string('color6_title', 'mod_stickynotes'),
        get_string('color6_descr', 'mod_stickynotes'),
        get_string('color6', 'mod_stickynotes'),
        null )
        );
    }
}
