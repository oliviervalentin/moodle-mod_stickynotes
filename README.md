# Sticky Notes #

Sticky Notes is an activity for creating a sticky notes wall for brainstormings, ranking...

# Features ##

On install, admin can choose 6 colors which will be used throughall activities in Moodle. Default colors
have been chosen to respect accessibility and colorblindness.

Adding a Sticky Notes activity is like adding any other activity in Moodle. Turn on editing mode and
add activity "Sticky Notes".

When using the activity, users can create, update and delete their own notes in different columns
(columns can only be created by teachers).

When creating or editing a note, users can place it where he wants : they can choose column,
and/or they can put it before or after another note.
Changing place can be done througu the editing form, or directly using drag and drop.

It gives possibility to "like" notes just by cliking a little heart icon.

When creating an activity, teacher can define several features or limitation :
- capability to create a note with different background color
- choose colors to use in activity between the 6 default colors
- add a meaning for every color
- limit the number of notes a user can create 
- limit the number of votes a user can do
- enable the rotate effect.

This activity is anonymous for student : no names appears. But teachers can choose to view creator names
for their own use.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/stickynotes

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2021 Olivier VALENTIN - Sebastien MEHR

Main icon design by Alexandra CODINA
JS Script - drag and drop dev : Sebastien MEHR.
Drad and drop uses sortable.js.

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

## Contributing ##
Any type of contribution, suggestions, feature requests are welcome. 
