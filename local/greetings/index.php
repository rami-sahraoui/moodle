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

require_once('../../config.php');
require_once($CFG->dirroot . '/local/greetings/lib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url(new moodle_url("/local/greetings/index.php"));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string("pluginname", "local_greetings"));
$PAGE->set_heading(get_string("pluginname", "local_greetings"));

$messageform = new \local_greetings\form\message_form();

echo $OUTPUT->header();

echo '<h3>' . local_greetings_get_greeting($USER) . '</h3>';

$messageform->display();

if ($data = $messageform->get_data()) {
    $message = required_param('message', PARAM_TEXT);
    echo $OUTPUT->heading($message, 4);
    var_dump($data);
}

echo $OUTPUT->footer();
