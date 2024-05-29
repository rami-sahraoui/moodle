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
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_greetings
 * @copyright   2024 Rami SAHRAOUI <sahraoui.rami.1@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\user;

/**
 * helper function to adapt greetings message according to localisation
 * @param /stdClass $user  the current user.
 * @return lang_string|string
 */
function local_greetings_get_greeting($user): lang_string|string {
    if ($user == null) {
        return get_string('greetinguser', 'local_greetings');
    }

    $country = $user->country;
    switch ($country) {
        case 'FR':
            $langstr = 'greetinguserfr';
            break;
        case 'TN':
            $langstr = 'greetinguserar';
            break;
        default:
            $langstr = 'greetingloggedinuser';
            break;
    }

    return get_string($langstr, 'local_greetings', fullname($user));
}

/**
 * Insert a link to index.php on the site front page navigation menu.
 *
 * @param navigation_node $frontpage Node representing the front page in the navigation tree.
 */
function local_greetings_extend_navigation_frontpage(navigation_node $frontpage) {
    $frontpage->add(
            get_string('pluginname', 'local_greetings'),
            new moodle_url('/local/greetings/index.php')
    );
}

/**
 * Add link to index.php into navigation block.
 *
 * @param global_navigation $root Node representing the global navigation tree.
 */
function local_greetings_extend_navigation(global_navigation $root) {
    $node = navigation_node::create(
            get_string('pluginname', 'local_greetings'),
            new moodle_url('/local/greetings/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            null,
            new pix_icon('t/message', '')
    );

    $node->showinflatnavigation = true;
    $root->add_node($node);
}
